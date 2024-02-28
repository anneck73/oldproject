<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\CouponBundle\Coupon;

use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Entity\AbstractEntity;
use Mealmatch\ApiBundle\Entity\Coupon\Coupon;
use Mealmatch\ApiBundle\Entity\Coupon\CouponDataInterface;

/**
 * The CouponData class provides access to the CouponData-Data and adds coupon logic.
 */
class CouponData implements CouponInterface
{
    /**
     * @var CouponDataInterface
     */
    private $couponEntity;
    /**
     * @var string
     */
    private $couponType;

    public function __construct(CouponDataInterface $entity, $couponType = ApiConstants::COUPON_TYPE_DEFAULT)
    {
        $this->couponEntity = $entity;
        $this->couponType = $couponType;
    }

    /**
     * Returns the contained coupon entity as and AbstractEntity.
     *
     * @return CouponDataInterface
     */
    public function getCouponEntity(): CouponDataInterface
    {
        return $this->couponEntity;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->couponType;
    }

    /**
     * @param string $couponType
     *
     * @return CouponData
     */
    public function setType(string $couponType): self
    {
        $this->couponType = $couponType;

        return $this;
    }

    public function getValue(): float
    {
        if ($this->couponEntity instanceof CouponDataInterface) {
            return $this->couponEntity->getValue();
        }
    }

    public function getCurrency(): string
    {
        return $this->couponEntity->getCurrency();
    }

    public function getStatus(): string
    {
        return $this->couponEntity->getStatus();
    }

    public function setStatus(string $status): CouponInterface
    {
        $this->couponEntity->setStatus($status);

        return $this;
    }

    public function getCouponCode(): string
    {
        return $this->couponEntity->getCode();
    }

    public function isActive(): bool
    {
        return 'active' === $this->couponEntity->getStatus();
    }

    public function isAvailable(): bool
    {
        return $this->couponEntity->getUsedAmount() < $this->couponEntity->getAvailableAmount();
    }

    public function getTitle(): ?string
    {
        return $this->couponEntity->getTitle();
    }

    public function getAvailableAmount(): ?int
    {
        return $this->couponEntity->getAvailableAmount();
    }

    public function getDescription(): ?string
    {
        return $this->couponEntity->getDescription();
    }

    public function redeem(int $amount = 1): void
    {
        $currentAvail = $this->couponEntity->getAvailableAmount();
        $newAvail = $currentAvail - $amount;
        $this->couponEntity->setAvailableAmount($newAvail);

        $currentUsed = $this->couponEntity->getUsedAmount();
        $newUsed = $currentUsed + $amount;
        $this->couponEntity->setUsedAmount($newUsed);
    }
}
