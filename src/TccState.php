<?php


namespace Tcc\TccTransaction;


class TccState
{
    /**
     * @var bool 事务状态
     */
    public $tccStatus;

    /**
     * @var bool 操作状态
     */
    public $optionStatus;

    /**
     * @var string 当前步数
     */
    public $optionStep;

    /**
     * @var TccOption[] 操作步骤
     */
    public $options = [];

    /**
     * @var array 操作响应
     */
    public $results = [];

    /**
     * @var array 操作编排 [ sync[1, 2], sync[3, 4], sync[5, 6]]
     */
    public $rely;

    /**
     * @var int 创建时间
     */
    public $createAt;

    public function __construct(bool $tccStatus = false, bool $optionStatus = false, string $optionStep = 'try')
    {
        $this->tccStatus = $tccStatus;
        $this->optionStatus = $optionStatus;
        $this->optionStep = $optionStep;
        $this->createAt = time();
    }
}