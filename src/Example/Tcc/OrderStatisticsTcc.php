<?php


namespace Tcc\TccTransaction\Example\Tcc;


use Tcc\TccTransaction\Example\Service\OrderService;
use Tcc\TccTransaction\TccOption;

class OrderStatisticsTcc extends TccOption
{

    public function try()
    {
        # 增加订单统计
        $service = new OrderService;
        $service->incOrderStatistics();
    }

    public function confirm()
    {
        # 空操作
    }

    public function cancel()
    {
        # 减少订单统计
        $service = new OrderService;
        $service->decOrderStatistics();
    }
}