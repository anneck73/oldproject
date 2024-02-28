<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Entity\Meal;

use Doctrine\ORM\Mapping as ORM;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Entity\AbstractEntity;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class MealJoinRequest does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 * @ORM\Entity()
 */
class MealJoinRequest extends AbstractEntity implements MealData
{
    /**
     * Many JoinRequest's have one Meal.
     *
     * @ORM\ManyToOne(targetEntity="Mealmatch\ApiBundle\Entity\Meal\BaseMeal", inversedBy="joinRequests")
     * @ORM\JoinColumn(name="meal_id", referencedColumnName="id", unique=false)
     */
    private $baseMeal;

    /**
     * @var string
     *
     * @ORM\Column(name="messageToHost", type="string", length=255)
     */
    private $messageToHost;

    /**
     * @var string
     *
     * @ORM\Column(name="messageToGuest", type="string", length=255, nullable=true)
     */
    private $messageToGuest;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=25)
     */
    private $status = 'CREATED';
    /**
     * @todo: Finish PHPDoc!
     *
     * @var int
     * @ORM\Column(name="extra_guest", type="integer", length=1)
     */
    private $extraGuest = 0;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var bool
     * @ORM\Column(name="accepted", type="boolean")
     */
    private $accepted = false;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var bool
     * @ORM\Column(name="denied", type="boolean")
     */
    private $denied = false;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var bool
     * @ORM\Column(name="payed", type="boolean")
     */
    private $payed = false;

    /**
     * @return int
     */
    public function getExtraGuest(): int
    {
        return $this->extraGuest;
    }

    /**
     * @param int $extraGuest
     *
     * @return MealJoinRequest
     */
    public function setExtraGuest(int $extraGuest): self
    {
        $this->extraGuest = $extraGuest;

        return $this;
    }

    public function getMeal()
    {
        return $this->getBaseMeal();
    }

    /**
     * @return mixed
     */
    public function getBaseMeal()
    {
        return $this->baseMeal;
    }

    /**
     * @param mixed $baseMeal
     *
     * @return MealJoinRequest
     */
    public function setBaseMeal($baseMeal)
    {
        $this->baseMeal = $baseMeal;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessageToHost()
    {
        return $this->messageToHost;
    }

    /**
     * @param string $messageToHost
     *
     * @return MealJoinRequest
     */
    public function setMessageToHost(string $messageToHost): self
    {
        $this->messageToHost = $messageToHost;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessageToGuest()
    {
        return $this->messageToGuest;
    }

    /**
     * @param string $messageToGuest
     *
     * @return MealJoinRequest
     */
    public function setMessageToGuest(string $messageToGuest): self
    {
        $this->messageToGuest = $messageToGuest;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $pStatus): self
    {
        if ($this->isPayed() && ApiConstants::JOIN_REQ_STATUS_PAYED === $pStatus) {
            $this->status = ApiConstants::JOIN_REQ_STATUS_PAYED;
        } else {
            if ($this->isAccepted() && ApiConstants::JOIN_REQ_STATUS_ACCEPTED === $pStatus) {
                $this->status = ApiConstants::JOIN_REQ_STATUS_ACCEPTED;
            } else {
                if ($this->isDenied() && ApiConstants::JOIN_REQ_STATUS_DENIED === $pStatus) {
                    $this->status = ApiConstants::JOIN_REQ_STATUS_DENIED;
                } else {
                    $this->status = $pStatus;
                }
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isAccepted(): bool
    {
        return $this->accepted;
    }

    /**
     * @param bool $accepted
     *
     * @return MealJoinRequest
     */
    public function setAccepted(bool $accepted): self
    {
        $this->accepted = $accepted;
        $this->setStatus(ApiConstants::JOIN_REQ_STATUS_ACCEPTED);

        return $this;
    }

    /**
     * @return bool
     */
    public function isDenied(): bool
    {
        return $this->denied;
    }

    /**
     * @param bool $denied
     *
     * @return MealJoinRequest
     */
    public function setDenied(bool $denied): self
    {
        $this->denied = $denied;
        $this->setStatus(ApiConstants::JOIN_REQ_STATUS_DENIED);

        return $this;
    }

    /**
     * @return bool
     */
    public function isPayed(): bool
    {
        return $this->payed;
    }

    /**
     * @param bool $payed
     *
     * @return MealJoinRequest
     */
    public function setPayed(bool $payed): self
    {
        $this->payed = $payed;
        $this->setStatus(ApiConstants::JOIN_REQ_STATUS_PAYED);

        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @return mixed
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @return mixed
     */
    public function getDeletedBy()
    {
        return $this->deletedBy;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
