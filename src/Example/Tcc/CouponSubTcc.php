<?php


namespace Tcc\TccTransaction\Example\Tcc;


use Tcc\TccTransaction\Example\Service\CouponService;
use Tcc\TccTransaction\TccOption;

class CouponSubTcc extends TccOption
{
    protected $couponId;

    public function try()
    {

        # 获取优惠券ID, 依赖 CouponLockTcc::class 操作返回
        $this->couponId = (int)($this->tcc->get(CouponLockTcc::class, [])['id'] ?? 0);
        if ($this->couponId) {

            # 占用优惠券
            $service = new CouponService;
            $service->useCoupon($this->couponId);
        }
    }

    public function confirm()
    {
        # 空操作
    }

    public function cancel()
    {
        if ($this->couponId) {

            # 解除优惠券
            $service = new CouponService;
            $service->unUseCoupon($this->couponId);
        }
    }
}
