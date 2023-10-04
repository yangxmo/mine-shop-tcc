<?php


namespace Tcc\TccTransaction\Example\Tcc;


use Tcc\TccTransaction\Example\Service\GoodsService;
use Tcc\TccTransaction\Exception\ServiceException;
use Tcc\TccTransaction\TccOption;

class GoodsLockTcc extends TccOption
{
    protected $goodsId;

    public function __construct(int $goodsId)
    {
        $this->goodsId = $goodsId;
    }

    /**
     * @throws ServiceException
     */
    public function try(): array
    {
        $service = new GoodsService;

        # 验证商品是否存在
        $goods = $service->getGoods($this->goodsId);

        # 锁定商品库存 -1
        $service->lockStock($this->goodsId);

        # 返回商品信息
        return $goods;
    }

    public function confirm()
    {
        // 空提交
    }

    public function cancel()
    {
        # 解锁商品库存
        $service = new GoodsService;
        $service->releaseStock($this->goodsId);
    }
}