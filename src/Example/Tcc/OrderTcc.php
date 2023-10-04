<?php


namespace Tcc\TccTransaction\Example\Tcc;


use Tcc\TccTransaction\Example\Service\OrderService;
use Tcc\TccTransaction\TccOption;

class OrderTcc extends TccOption
{
    protected $orderId;

    public function try()
    {
        # 获取依赖参数 优惠券, 商品
        $goods = $this->tcc->get(GoodsLockTcc::class);
        $coupon = $this->tcc->get(CouponLockTcc::class);

        # 创建订单
        $service = new OrderService;
        $order = $service->createOrder($goods, $coupon);
        $this->orderId = (int)$order['id'];

        # 返回订单
        return $order;
    }

    public function confirm()
    {
        # 空提交
    }

    public function cancel()
    {
        # 删除订单
        $service = new OrderService;
        $service->deleteOrder($this->orderId);
    }
}