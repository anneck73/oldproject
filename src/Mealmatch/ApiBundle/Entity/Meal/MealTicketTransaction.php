<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Entity\Meal;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Mealmatch\ApiBundle\Entity\AbstractEntity;

/**
 * The MealTicketTransaction (MTT) is used to store the transaction values from Mangopay.
 * Each mangopay transaction is unique and identifies itself with the "ressourceid": ->getResourceId().
 * Additionally the field "processed" indicates if a mangopay signal was processed further by Mealmatch logic.
 *
 *
 * @ORM\Table(name="meal_ticket_transaction")
 * @ORM\Entity(repositoryClass="Mealmatch\ApiBundle\Repository\Meal\MealTicketTransactionRepository")
 */
class MealTicketTransaction extends AbstractEntity implements MealData
{
    /**
     * @var bool
     * @ORM\Column(name="processed", type="boolean")
     */
    private $processed = false;

    /**
     * The currently active user in the session, during payment.
     *
     * System user is executing changes from
     *      remote signals (Mangopay Hook Events)
     *      cron based mealmatch jobs (PayOut to Restaurant)
     *
     * @var int|null
     *
     * @ORM\Column(name="user_id", type="integer", nullable=true)
     */
    private $userId;

    /**
     * @var BaseMealTicket
     * @ORM\ManyToOne(targetEntity="Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket", inversedBy="transactions")
     */
    private $mealTicket;

    /**
     * The Status of the Ticket:
     * created|cancelled|pending|sold.
     *
     * @var string
     * @ORM\Column(name="paymentStatus", type="string", length=64, nullable=true)
     */
    private $paymentStatus;

    /**
     * Type of payment.
     *
     * @var string
     * @ORM\Column(name="paymentType", type="string", length=64, nullable=true)
     */
    private $paymentType;

    /**
     * TransactionType.
     *
     * @var string
     * @ORM\Column(name="transactionType", type="string", length=64, nullable=true)
     */
    private $transactionType;

    /**
     * ResourceId.
     *
     * @var string
     * @ORM\Column(name="resourceId", type="string", length=64, nullable=true)
     */
    private $resourceId;

    /**
     * MangopayNotifiedDate.
     *
     * @var string|null
     *
     * @ORM\Column(name="mango_notified_date", type="string", length=64, nullable=true)
     */
    private $mangoNotifiedDate;

    /**
     * @var string|null
     *
     * @ORM\Column(name="mango_obj", type="string", length=32, nullable=true)
     */
    private $mangoObj;
    /**
     * @var string|null
     *
     * @ORM\Column(name="mango_event", type="string", length=32, nullable=true)
     */
    private $mangoEvent;
    /**
     * @var string|null
     *
     * @ORM\Column(name="mango_event_type", type="string", length=32, nullable=true)
     */
    private $mangoEventType;

    /**
     * payOutSourceResourceId.
     *
     * @var string|null
     * @ORM\Column(name="payOutSourceResourceId", type="string", length=64, nullable=true)
     */
    private $payOutSourceResourceId;

    public function __toString()
    {
        return 'MTT#'.$this->getResourceId().'('.$this->getPaymentStatus().')';
    }

    /**
     * @return int|null
     */
    public function getUserID(): ?int
    {
        return $this->userId;
    }

    /**
     * @param int|null $userId
     *
     * @return $userId
     */
    public function setUserID(?int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Returns the ID of the associated mealticket, or null.
     *
     * @return int|null
     */
    public function getMealTicketId(): ?int
    {
        if (null !== $this->mealTicket) {
            return $this->mealTicket->getId();
        }

        return null;
    }

    /**
     * Returns the DateTime of the creation of this transaction, or null.
     *
     * @return DateTime|null
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * Returns the transaction type (Mangopay) of this transaction, or null.
     *
     * @return string|null
     */
    public function getTransactionType(): ?string
    {
        return $this->transactionType;
    }

    /**
     * @param string $status
     * @param mixed  $transactionType
     *
     * @return string
     */
    public function setTransactionType($transactionType): self
    {
        $this->transactionType = $transactionType;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaymentType(): ?string
    {
        return $this->paymentType;
    }

    /**
     * @param string $paymentType
     *
     * @return string
     */
    public function setPaymentType(string $paymentType): self
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    /**
     * @param string $paymentStatus
     *
     * @return string
     */
    public function setPaymentStatus(string $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getResourceId(): ?string
    {
        return $this->resourceId;
    }

    /**
     * @param string $payinStatus
     *
     * @return string
     */
    public function setResourceId(string $resourceId): self
    {
        $this->resourceId = $resourceId;

        return $this;
    }

    /*
     * @return \string|null
     */
    public function getPayOutSourceResourceId(): ?string
    {
        return $this->payOutSourceResourceId;
    }

    /*
     * @param string|null $payOutSourceResourceId
     *
     * @return $payOutSourceResourceId
     */
    public function setPayOutSourceResourceId(string $payOutSourceResourceId): self
    {
        $this->payOutSourceResourceId = $payOutSourceResourceId;

        return $this;
    }

    /**
     * @return BaseMealTicket|null
     */
    public function getMealTicket(): ?BaseMealTicket
    {
        return $this->mealTicket;
    }

    /**
     * @param BaseMealTicket $mealTicket
     *
     * @return MealTicketTransaction
     */
    public function setMealTicket(BaseMealTicket $mealTicket): self
    {
        $mealTicket->addTransaction($this);

        $this->mealTicket = $mealTicket;

        return $this;
    }

    /**
     * @return bool
     */
    public function isProcessed(): bool
    {
        return $this->processed;
    }

    /**
     * @param bool $processed
     *
     * @return MealTicketTransaction
     */
    public function setProcessed(bool $processed): self
    {
        $this->processed = $processed;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMangoObj(): ?string
    {
        return $this->mangoObj;
    }

    /**
     * @param string $mangoObj
     *
     * @return MealTicketTransaction
     */
    public function setMangoObj(string $mangoObj): self
    {
        $this->mangoObj = $mangoObj;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMangoEvent(): ?string
    {
        return $this->mangoEvent;
    }

    /**
     * @param string $mangoEvent
     *
     * @return MealTicketTransaction
     */
    public function setMangoEvent(string $mangoEvent): self
    {
        $this->mangoEvent = $mangoEvent;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMangoEventType(): ?string
    {
        return $this->mangoEventType;
    }

    /**
     * @param string $mangoEventType
     *
     * @return MealTicketTransaction
     */
    public function setMangoEventType(string $mangoEventType): self
    {
        $this->mangoEventType = $mangoEventType;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMangoNotifiedDate(): ?string
    {
        return $this->mangoNotifiedDate;
    }

    /**
     * @param string|null $mangoNotifiedDate
     *
     * @return MealTicketTransaction
     */
    public function setMangoNotifiedDate(?string $mangoNotifiedDate): self
    {
        $this->mangoNotifiedDate = $mangoNotifiedDate;

        return $this;
    }

    public function hasMealticket(): bool
    {
        return null !== $this->mealTicket;
    }
}
