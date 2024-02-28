<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Entity\Coupon;

interface CouponDataInterface
{
    /**
     * @return string
     */
    public function getCode(): ?string;

    /**
     * @param string $code
     *
     * @return Coupon
     */
    public function setCode(string $code);

    /**
     * @return int
     */
    public function getAvailableAmount(): int;

    /**
     * @param int $availableAmount
     *
     * @return Coupon
     */
    public function setAvailableAmount(int $availableAmount);

    /**
     * @return int
     */
    public function getUsedAmount(): int;

    /**
     * @param int $usedAmount
     *
     * @return Coupon
     */
    public function setUsedAmount(int $usedAmount);

    /**
     * @return float
     */
    public function getValue(): float;

    /**
     * @param float $value
     *
     * @return Coupon
     */
    public function setValue(float $value);

    /**
     * @return string
     */
    public function getCurrency(): string;

    /**
     * @param string $currency
     *
     * @return Coupon
     */
    public function setCurrency(string $currency);

    /**
     * @return string
     */
    public function getStatus(): string;

    /**
     * @param string $status
     *
     * @return Coupon
     */
    public function setStatus(string $status);

    /**
     * @return string
     */
    public function getLanguage(): string;

    /**
     * @param string $language
     *
     * @return Coupon
     */
    public function setLanguage(string $language);

    /**
     * @return string
     */
    public function getTitle(): ?string;

    /**
     * @param string $title
     *
     * @return Coupon
     */
    public function setTitle(string $title);

    /**
     * @return string
     */
    public function getDescription(): ?string;

    /**
     * @param string $description
     *
     * @return Coupon
     */
    public function setDescription(string $description);
}
