<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\CouponBundle\Coupon;

use Doctrine\Common\Collections\Collection;
use Mealmatch\ApiBundle\Entity\Coupon\UsedCoupon;

interface UsedCouponInterface extends CouponInterface
{
    /**
     * @return string|null
     */
    public function getUsedCode(): ?string;

    /**
     * @return Collection
     */
    public function getCouponProfiles(): Collection;

    /**
     * @return bool
     */
    public function isRedeemed(): bool;

    /**
     * @return bool
     */
    public function isClaimed(): bool;

    public function getUsedCouponEntity(): UsedCoupon;
}
