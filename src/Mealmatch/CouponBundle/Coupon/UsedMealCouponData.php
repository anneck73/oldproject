<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\CouponBundle\Coupon;

use Doctrine\Common\Collections\Collection;
use Mealmatch\ApiBundle\Entity\Coupon\MealCoupon as CouponEntity;
use Mealmatch\ApiBundle\Entity\Coupon\UsedCoupon as UsedCouponEntity;

/**
 * The CouponData class provides access to the CouponData-Data and adds coupon logic.
 */
class UsedMealCouponData extends CouponData implements UsedCouponInterface
{
    /**
     * @var UsedCouponEntity
     */
    private $usedCouponEntity;

    public function __construct(CouponEntity $entity, UsedCouponEntity $usedCouponEntity)
    {
        parent::__construct($entity);
        $this->usedCouponEntity = $usedCouponEntity;
    }

    public function getUsedCode(): ?string
    {
        return $this->usedCouponEntity->getCode();
    }

    public function getCouponProfiles(): Collection
    {
        $this->usedCouponEntity->getCouponProfiles();
    }

    public function isRedeemed(): bool
    {
        return $this->usedCouponEntity->isRedeemed();
    }

    public function isClaimed(): bool
    {
        return $this->usedCouponEntity->isClaimed();
    }

    public function getUsedCouponEntity(): UsedCouponEntity
    {
        return $this->usedCouponEntity;
    }
}
