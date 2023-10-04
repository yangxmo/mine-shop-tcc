<?php


namespace Tcc\TccTransaction;

use Tcc\TccTransaction\Exception\TccOptionParamException;
use Tcc\TccTransaction\Exception\TccTraceException;
use Tcc\TccTransaction\Util\Di;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Redis\Redis;
use Hyperf\Utils\Parallel;

class Tcc
{
    /**
     * @var string
     */
    protected $tccId; # 事务ID

    /**
     * @var TccState 状态
     */
    protected $state;

    /**
     * @var Redis
     */
    protected $redis;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * Tcc constructor.
     * @param string|null $tccId
     * @param TccState|null $state
     */
    public function __construct(?string $tccId = null, ?TccState $state = null)
    {
        if ($tccId) {
            $this->tccId = $tccId;
            $this->state = $state;
            // 重新关联自身
            foreach ($this->state->options as $option) {
                $option->setTcc($this);
            }
        } else {
            $this->tccId = (string)Di::idGenerator()->generate();
            $this->state = new TccState(false, false, 'try');
        }
        $this->redis = Di::redis();
        $this->logger = Di::logger();
    }

    /**
     * 增加操作
     * @param string|integer $key
     * @param TccOption $tcc
     * @return $this
     */
    public function tcc($key, TccOption $tcc)
    {
        $tcc->setKey($key);
        $this->state->options[$key] = $tcc;
        return $this;
    }

    /**
     * 依赖关系
     * @param array $rely 被依赖主键
     * @return $this
     */
    public function rely(array $rely)
    {
        $this->state->rely = $rely;
        return $this;
    }

    /*
     * 开启事务
     */
    public function begin()
    {
        // 读取编排
        if (!$this->state->rely) {
            $this->state->rely = [array_keys($this->state->options)];
        }

        // 推送任务
        $this->pushState(false, false, 'try');
        $this->pushMessage();
        $this->bindOptions($this);

        try {
            $this->runOptionTry();      # 开启事务
            $this->runOptionConfirm();  # 确认提交
        } catch (\Throwable $e) {
            $this->runOptionCancel($e);   # 回滚事务
        }
    }

    /**
     * 绑定操作
     * @param Tcc|null $tcc
     */
    protected function bindOptions(?Tcc $tcc)
    {
        foreach ($this->state->options as $option) {
            $option->setTcc($tcc);
        }
    }

    /*
     * 执行事务启动操作
     */
    public function runOptionTry()
    {
        // 根据流程编排去执行
        foreach ($this->state->rely as $syncs) {
            $parallel = new Parallel;
            foreach ($syncs as $key) {
                $parallel->add(function () use ($key) {
                    $option = $this->state->options[$key];
                    $this->state->results[$key] = $option->try();
                    $option->setStep('try');
                });
            }
            $parallel->wait();
        }
    }

    /*
     * 执行事务提交操作
     */
    public function runOptionConfirm()
    {
        // 根据流程编排去执行
        foreach ($this->state->rely as $syncs) {
            $parallel = new Parallel;
            foreach ($syncs as $key) {
                $parallel->add(function () use ($key) {
                    $option = $this->state->options[$key];
                    if ($option->getStep() == 'try') {
                        $option->confirm();
                        $option->setStep('confirm');
                    }
                });
            }
            $parallel->wait();
        }

        // 推送 处理成功消息
        $this->pushState(true, true, 'confirm');
    }

    /*
     * 执行事务回滚操作
     */
    public function runOptionCancel(\Throwable $tryException = null)
    {
        try {
            // 根据流程编排倒序去执行
            foreach (array_reverse($this->state->rely) as $syncs) {
                $parallel = new Parallel;
                foreach (array_reverse($syncs) as $key) {
                    $parallel->add(function () use ($key) {
                        $option = $this->state->options[$key];
                        switch ($option->getStep()) {
                            case 'try':
                            case 'confirm':
                                $option->cancel();
                                $option->setStep('cancel');
                                break;
                        }
                    });
                }
                $parallel->wait();
            }

            // 推送 处理业务回滚成功消息
            $this->pushState(true, true, 'cancel');
        } catch (\Throwable $cancelException) {
            // 推送 处理业务回滚成功消息
            $this->pushState(true, false, 'cancel');
            // 抛出错误
            if ($tryException) {
                throw new TccTraceException($tryException, $cancelException);
            } else {
                throw $cancelException;
            }
        }

        // 抛出错误
        if ($tryException) {
            throw $tryException;
        }
    }

    /*
     * 推送消息
     */
    protected function pushMessage()
    {
        Di::nsq()->publish(Di::config('tcc.nsq_topic', 'tcc'), $this->tccId, Di::config('tcc.nsq_detection_time', 5));
        $this->logger->info('[TCC事务] 推送通知 ' . $this->tccId);
    }

    /*
     * 推送状态
     */
    protected function pushState(bool $tccStatus = false, bool $optionStatus = false, string $optionStep = 'try')
    {
        $this->state->tccStatus = $tccStatus;
        $this->state->optionStatus = $optionStatus;
        $this->state->optionStep = $optionStep;

        // 当推送完成后解绑操作-减轻存储负担
        if ($tccStatus) {
            $this->bindOptions(null);
        }

        // 当全部成功后删除操作和返回-减轻存储负担
        if ($optionStatus) {
            $state = clone $this->state;
            $state->options = [];
            $state->results = [];
            $state->rely = [];
            $state = serialize($state);
        } else {
            $state = serialize($this->state);
        }

        $this->redis->hSet('tcc', $this->tccId, $state);
        $this->logger->info('[TCC事务] 推送状态 ' . $optionStep);
    }

    /**
     * 获取响应参数
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        if (isset($this->state->results[$key])) {
            return $this->state->results[$key];
        }
        foreach ($this->state->options as $option) {
            if (get_class($option) == $key) {
                return $this->state->results[$option->getKey()];
            }
        }
        return $default;
    }
}