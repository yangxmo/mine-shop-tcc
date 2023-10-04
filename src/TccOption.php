<?php


namespace Tcc\TccTransaction;


use Tcc\TccTransaction\Util\Di;

abstract class TccOption
{
    /** @var string 返回主键 */
    protected $key;
    /** @var string 当前步骤 try, confirm, cancel */
    protected $step;
    /** @var Tcc */
    protected $tcc;

    /**
     * @param string $key
     */
    public function setKey(string $key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $step
     */
    public function setStep(string $step)
    {
        $this->step = $step;
        Di::logger()->info('[TCC事务] 任务项 ' . get_class($this) . '#' . $step);
    }

    /**
     * @return string
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * @param Tcc $tcc
     */
    public function setTcc(Tcc $tcc = null)
    {
        $this->tcc = $tcc;
    }

    /**
     * @return string
     */
    public function getTcc()
    {
        return $this->tcc;
    }

    public abstract function try();

    public abstract function confirm();

    public abstract function cancel();
}