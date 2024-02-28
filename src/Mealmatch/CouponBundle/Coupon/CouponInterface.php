<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\CouponBundle\Coupon;

use Mealmatch\ApiBundle\Entity\Coupon\CouponDataInterface;

interface CouponInterface
{
    public function getCouponEntity(): CouponDataInterface;

    public function getType(): string;

    public function getValue(): float;

    public function getCurrency(): string;

    public function getStatus(): string;

    public function setStatus(string $status): self;

    public function getCouponCode(): string;

    public function isAvailable(): bool;

    public function isActive(): bool;

    public function redeem(int $times): void;

    public function getTitle(): ?string;

    public function getAvailableAmount(): ?int;

    public function getDescription(): ?string;
}
