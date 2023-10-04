<?php


namespace Tcc\TccTransaction\Coordinator;


use Tcc\TccTransaction\Exception\Handle;
use Tcc\TccTransaction\Tcc;
use Tcc\TccTransaction\TccState;
use Tcc\TccTransaction\Util\Di;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Nsq\AbstractConsumer;
use Hyperf\Nsq\Message;
use Hyperf\Nsq\Nsq;
use Hyperf\Nsq\Result;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;
use Throwable;


class TccCoordinator extends AbstractConsumer
{

    /**
     * @var Nsq
     */
    protected $nsq;

    /**
     * @var Redis
     */
    protected $redis;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var Handle
     */
    protected $exception;

    /**
     * @var integer
     */
    protected $delay;

    /**
     * @var string
     */
    protected $topic;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->nsq = Di::nsq();
        $this->redis = Di::redis();
        $this->logger = Di::logger();
        $this->exception = Di::exception();
        $this->delay = Di::config('tcc.nsq_detection_time', 5);
        $this->topic = Di::config('tcc.nsq_topic', 'tcc');
    }

    /**
     * @param Message $message
     * @return string|null
     * @throws \RedisException
     */
    public function consume(Message $message): ?string
    {
        $tccId = (string)$message->getBody();
        $state = $this->getState($tccId);
        $this->logger->info('[TCC事务管理] 开始处理 ' . $tccId);

        if ($state instanceof TccState) {

            // 如果事务未处理完毕则延迟检测
            if (!$state->tccStatus) {
                $this->nsq->publish($this->topic, $tccId, $this->delay);
                $this->logger->info('[TCC事务管理] 重发未完成 ' . $tccId);
                return Result::ACK;
            }

            $this->logger->info('[TCC事务管理] 执行状态 ' . $tccId . '#' . $state->optionStep);

            // 如果操作失败则回滚
            if ($state->optionStatus) {
                $this->delState($tccId); # 删除记录
            } else {
                try {
                    // 处理失败的事务并回滚
                    $tcc = new Tcc($tccId, $state);
                    $tcc->runOptionCancel();  # 重试取消
                    $this->delState($tccId);  # 删除记录
                    $this->logger->info('[TCC事务管理] 回滚成功 ' . $tccId);
                } catch (\Throwable $e) {
                    $this->pushNotify($tccId, $state, $e);   # 推送通知
                    $this->delState($tccId);                 # 删除记录
                    $this->logger->error('[TCC事务管理] 回滚失败 ' . $tccId);
                }
            }
        } else {
            $this->logger->info('[TCC事务管理] 无效的状态 ' . $tccId);
        }

        return Result::ACK;
    }

    /**
     * @param string $tccId
     * @param TccState $state
     * @param Throwable $e
     */
    protected function pushNotify(string $tccId, TccState $state, \Throwable $e)
    {
        foreach ($state->options as $option) {
            $option->setTcc(null);
        }
        // 调用提供者处理
        $this->exception->handle($tccId, $state, $e);
    }

    /**
     * @param string $tccId
     * @throws \RedisException
     */
    protected function delState(string $tccId)
    {
        $this->redis->hDel('tcc', $tccId);
    }

    /**
     * @param string $tccId
     * @return TccState|null
     * @throws \RedisException
     */
    protected function getState(string $tccId): ?TccState
    {
        $state = (string)$this->redis->hGet('tcc', $tccId);
        if ($state) {
            return unserialize($state);
        }
        return null;
    }
}