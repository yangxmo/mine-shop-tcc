<?php


namespace Tcc\TccTransaction\Exception;



class TccTraceException extends \Exception
{
    public $traces;

    public function __construct(\Throwable $try, \Throwable $cancel)
    {
        parent::__construct('TCC事务回滚异常', 0, null);
        foreach (['try' => $try, 'cancel' => $cancel] as $key => $exception) {
            if ($exception) {
                $this->traces[$key] = [
                    'class' => get_class($exception),
                    'message' => $exception->getMessage(),
                    'location' => $exception->getLine() . '#' . $exception->getFile(),
                ];
            } else {
                $this->traces[$key] = null;
            }
        }
    }
}