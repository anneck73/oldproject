<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Entity\Coupon;

use Doctrine\ORM\Mapping as ORM;
use Mealmatch\ApiBundle\Entity\AbstractEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * The MealCoupon class enables all BaseMeals to have custom meal-specific coupons.
 *
 * @ORM\Entity()
 * @ORM\Table(name="meal_coupon")
 * @UniqueEntity("code")
 */
class MealCoupon extends AbstractEntity implements CouponDataInterface
{
    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     */
    protected $title = 'MealCoupon-TYPE-Title';

    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     */
    protected $description = 'MealCouponDescription';

    /**
     * @var string
     * @ORM\Column(type="string", length=120, unique=true)
     */
    protected $code;
    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $availableAmount = 1;
    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $usedAmount = 0;
    /**
     * @var float
     * @ORM\Column(type="float")
     */
    protected $value = 0.0;

    /**
     * ISO Code currency.
     *
     * @var string
     * @ORM\Column(type="string", length=3)
     */
    protected $currency = 'EUR';

    /**
     * ISO language currency.
     *
     * @var string
     * @ORM\Column(type="string", length=12)
     */
    protected $language = 'de';

    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     */
    protected $status = 'new';

    /**
     * @return string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return Coupon
     */
    public function setCode(string $code)
    {
        $this->code = $code;
    }

    /**
     * @return int
     */
    public function getAvailableAmount(): int
    {
        return $this->availableAmount;
    }

    /**
     * @param int $availableAmount
     *
     * @return Coupon
     */
    public function setAvailableAmount(int $availableAmount)
    {
        $this->availableAmount = $availableAmount;
    }

    /**
     * @return int
     */
    public function getUsedAmount(): int
    {
        return $this->usedAmount;
    }

    /**
     * @param int $usedAmount
     *
     * @return Coupon
     */
    public function setUsedAmount(int $usedAmount)
    {
        $this->usedAmount = $usedAmount;
    }

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * @param float $value
     *
     * @return Coupon
     */
    public function setValue(float $value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return Coupon
     */
    public function setCurrency(string $currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return Coupon
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @param string $language
     *
     * @return Coupon
     */
    public function setLanguage(string $language)
    {
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Coupon
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Coupon
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }
}
