<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Entity\User\Profiles;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mealmatch\ApiBundle\Entity\AbstractEntity;
use Mealmatch\ApiBundle\Entity\Coupon\UsedCoupon;

/**
 * Class CouponProfile.
 *
 * @ORM\Entity()
 */
class CouponProfile extends AbstractEntity
{
    /**
     * The unique ID of the entity.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Many users used coupon codes.
     *
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="Mealmatch\ApiBundle\Entity\Coupon\UsedCoupon", inversedBy="couponProfiles")
     * @ORM\JoinTable(name="coupon_code_to_coupont_profile")
     */
    private $usedCouponCodes;

    public function __toString()
    {
        return __CLASS__.$this->getId();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Collection
     */
    public function getUsedCouponCodes(): Collection
    {
        return $this->usedCouponCodes;
    }

    /**
     * @param Collection $usedCouponCodes
     *
     * @return CouponProfile
     */
    public function setUsedCouponCodes($usedCouponCodes)
    {
        $this->usedCouponCodes = $usedCouponCodes;

        return $this;
    }

    /**
     * @param UsedCoupon $couponCode
     *
     * @return CouponProfile
     */
    public function addUsedCouponCode(UsedCoupon $couponCode): self
    {
        $this->usedCouponCodes->add($couponCode);

        return $this;
    }
}
