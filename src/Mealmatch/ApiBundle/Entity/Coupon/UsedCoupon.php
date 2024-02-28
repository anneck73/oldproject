<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Entity\Coupon;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mealmatch\ApiBundle\Entity\AbstractEntity;

/**
 * Class CouponCode.
 *
 * @ORM\Entity()
 */
class UsedCoupon extends AbstractEntity
{
    /**
     * @var string
     * @ORM\Column(type="string", length=120)
     */
    protected $code;

    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     */
    protected $title;

    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     */
    protected $description;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $redeemed = false;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $claimed = false;

    /**
     * @var bool;
     * @ORM\Column(type="boolean")
     */
    protected $transfered = false;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected $transferResourceId;

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
     * @var string
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected $type;

    /**
     * A CouponCode is connected to MealTickets.
     *
     * @var Collection
     * @ORM\OneToMany(targetEntity="Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket",
     *     mappedBy="coupon",
     *     cascade={"persist","remove"})
     */
    protected $mealTickets;

    /**
     * Coupons are associated to many CouponProfiles.
     *
     * @var Collection
     * @ORM\ManyToMany(targetEntity="Mealmatch\ApiBundle\Entity\User\Profiles\CouponProfile", mappedBy="usedCouponCodes")
     */
    private $couponProfiles;

    public function __toString()
    {
        return 'UsedCoupon#'.$this->getId().' ('.$this->getTitle().')';
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return UsedCoupon
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCouponProfiles(): \Doctrine\Common\Collections\Collection
    {
        return $this->couponProfiles;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $couponProfiles
     *
     * @return UsedCoupon
     */
    public function setCouponProfiles(\Doctrine\Common\Collections\Collection $couponProfiles): self
    {
        $this->couponProfiles = $couponProfiles;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRedeemed(): bool
    {
        return $this->redeemed;
    }

    /**
     * @param bool $redeemed
     *
     * @return UsedCoupon
     */
    public function setRedeemed(bool $redeemed): self
    {
        $this->redeemed = $redeemed;

        return $this;
    }

    /**
     * @return bool
     */
    public function isClaimed(): bool
    {
        return $this->claimed;
    }

    /**
     * @param bool $claimed
     *
     * @return UsedCoupon
     */
    public function setClaimed(bool $claimed): self
    {
        $this->claimed = $claimed;

        return $this;
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
     * @return UsedCoupon
     */
    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Coupon
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Coupon
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTransfered(): bool
    {
        return $this->transfered;
    }

    /**
     * @param bool $transfered
     *
     * @return UsedCoupon
     */
    public function setTransfered(bool $transfered): self
    {
        $this->transfered = $transfered;

        return $this;
    }

    /**
     * @return string
     */
    public function getTransferResourceId(): ?string
    {
        return $this->transferResourceId;
    }

    /**
     * @param string $transferResourceId
     *
     * @return UsedCoupon
     */
    public function setTransferResourceId(string $transferResourceId): self
    {
        $this->transferResourceId = $transferResourceId;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getMealTickets(): Collection
    {
        return $this->mealTickets;
    }

    /**
     * @param Collection $mealTickets
     *
     * @return UsedCoupon
     */
    public function setMealTickets(Collection $mealTickets): self
    {
        $this->mealTickets = $mealTickets;

        return $this;
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
     * @return UsedCoupon
     */
    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return UsedCoupon
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
