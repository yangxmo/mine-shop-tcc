<?php


namespace Tcc\TccTransaction\Example\Tcc;


use Tcc\TccTransaction\Example\Service\GoodsService;
use Tcc\TccTransaction\TccOption;

class GoodsSubTcc extends TccOption
{
    protected $goodsId;

    public function try()
    {
        # 获取商品信息, 依赖 GoodsLockTcc::class 操作返回
        $goods = $this->tcc->get(GoodsLockTcc::class);
        $this->goodsId = (int)$goods['id'];

        # 扣除商品库存, 增加销量, 解除锁定
        $service = new GoodsService;
        $service->subStock($this->goodsId);
    }

    public function confirm()
    {
        # 空操作
    }

    public function cancel()
    {
        # 增加商品库存, 减少销量, 恢复锁定
        $service = new GoodsService;
        $service->subStock($this->goodsId);
    }
}