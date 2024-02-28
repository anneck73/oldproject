<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Entity\Coupon;

use Doctrine\ORM\Mapping as ORM;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Entity\AbstractEntity;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;

/**
 * Class RedeemRequest.
 *
 * @ORM\Entity()
 */
class RedeemRequest extends AbstractEntity
{
    /**
     * @var BaseMealTicket
     * @ORM\ManyToOne(targetEntity="Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket",inversedBy="redeemRequests")
     */
    private $mealTicket;

    /**
     * @var string
     * @ORM\Column(type="string",length=64, nullable=true)
     */
    private $codeString = '#';

    /**
     * @var string
     * @ORM\Column(type="string",length=64)
     */
    private $status = ApiConstants::REDEEM_REQ_STATUS_NEW;

    /**
     * @return BaseMealTicket
     */
    public function getMealTicket(): BaseMealTicket
    {
        return $this->mealTicket;
    }

    /**
     * @param BaseMealTicket $mealTicket
     *
     * @return RedeemRequest
     */
    public function setMealTicket(BaseMealTicket $mealTicket): self
    {
        $this->mealTicket = $mealTicket;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodeString(): ?string
    {
        return $this->codeString;
    }

    /**
     * @param string $codeString
     *
     * @return RedeemRequest
     */
    public function setCodeString(string $codeString): self
    {
        $this->codeString = $codeString;

        return $this;
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
     * @return RedeemRequest
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
