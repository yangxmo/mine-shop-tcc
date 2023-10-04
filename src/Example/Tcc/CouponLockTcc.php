<?php


namespace Tcc\TccTransaction\Example\Tcc;


use Tcc\TccTransaction\Example\Service\CouponService;
use Tcc\TccTransaction\Exception\ServiceException;
use Tcc\TccTransaction\TccOption;

class CouponLockTcc extends TccOption
{
    protected $couponId;

    public function __construct(int $couponId)
    {
        $this->couponId = $couponId;
    }

    /**
     * @throws ServiceException
     */
    public function try(): ?array
    {
        # 验证是否有优惠券
        if ($this->couponId <= 0) {
            return null;
        }
        $service = new CouponService;
        # 获取优惠券
        $coupon = $service->getCoupon($this->couponId);
        # 锁定优惠券
        $service->lockCoupon($this->couponId);
        # 返回优惠券信息
        return $coupon;
    }

    public function confirm()
    {
        // 空提交
    }

    public function cancel()
    {
        # 验证是否有优惠券
        if ($this->couponId <= 0) {
            return;
        }

        # 解锁优惠券
        $service = new CouponService;
        $service->releaseCoupon($this->couponId);
    }
}