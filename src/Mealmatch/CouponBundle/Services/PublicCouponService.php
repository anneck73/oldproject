<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\CouponBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;

/**
 * Class PublicCouponService.
 */
class PublicCouponService
{
    /**
     * @var CouponService
     */
    private $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    /**
     * @return CouponService
     */
    public function getCouponService(): CouponService
    {
        return $this->couponService;
    }

    public function redeem(BaseMealTicket $mealTicket, string $code): array
    {
        return $this->couponService->redeem($mealTicket, $code);
    }

    public function listAll(): ArrayCollection
    {
        return $this->couponService->listAll();
    }
}
