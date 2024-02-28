<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Mealmatch\ApiBundle\MealMatch\Traits\Hashable;

/**
 * JoinRequest.
 *
 * @ORM\Table(name="join_request")
 * @ORM\Entity(repositoryClass="MMApiBundle\Repository\JoinRequestRepository")
 */
class JoinRequest
{
    use ORMBehaviors\Blameable\Blameable;
    use
        ORMBehaviors\Sortable\Sortable;
    use
        ORMBehaviors\Timestampable\Timestampable;
    use
        Hashable;

    public static $STATUS_CREATED = 'CREATED';
    public static $STATUS_ACCEPTED = 'ACCEPTED';
    public static $STATUS_DENIED = 'DENIED';
    public static $STATUS_PAYED = 'PAYED';
    public static $STATUS_PAYMENT_FAILED = 'PAYMENT_FAILED';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Many JoinRequest's have one Meal.
     *
     * @ORM\ManyToOne(targetEntity="MMApiBundle\Entity\Meal", inversedBy="joinRequests")
     * @ORM\JoinColumn(name="meal_id", referencedColumnName="id", unique=false)
     */
    private $meal;

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
    private $status = '';

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

    public function __construct()
    {
        $this->initHash();
    }

    public function __toString()
    {
        return __CLASS__.$this->getId().$this->getStatus()."\n";
    }

    /**
     * @return Meal
     */
    public function getMeal(): Meal
    {
        return $this->meal;
    }

    /**
     * @param Meal $meal
     *
     * @return JoinRequest
     */
    public function setMeal(Meal $meal): self
    {
        $this->meal = $meal;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMessageToHost()
    {
        return $this->messageToHost;
    }

    /**
     * @param string $messageToHost
     *
     * @return JoinRequest
     */
    public function setMessageToHost(string $messageToHost): self
    {
        $this->messageToHost = $messageToHost;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMessageToGuest()
    {
        return $this->messageToGuest;
    }

    /**
     * @param string $messageToGuest
     *
     * @return JoinRequest
     */
    public function setMessageToGuest(string $messageToGuest): self
    {
        $this->messageToGuest = $messageToGuest;

        return $this;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return string
     */
    public function getStatus(): string
    {
        $returnValue = self::$STATUS_CREATED;

        if ($this->isPayed()) {
            $returnValue = self::$STATUS_PAYED;
        } else {
            if ($this->isAccepted()) {
                $returnValue = self::$STATUS_ACCEPTED;
            } else {
                if ($this->isDenied()) {
                    $returnValue = self::$STATUS_DENIED;
                }
            }
        }

        return $returnValue;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $pStatus
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return JoinRequest
     */
    public function setStatus(string $pStatus): self
    {
        if ($this->isPayed() && $pStatus === self::$STATUS_PAYED) {
            $this->status = self::$STATUS_PAYED;
        } else {
            if ($this->isAccepted() && $pStatus === self::$STATUS_ACCEPTED) {
                $this->status = self::$STATUS_ACCEPTED;
            } else {
                if ($this->isDenied() && $pStatus === self::$STATUS_DENIED) {
                    $this->status = self::$STATUS_DENIED;
                } else {
                    // we are silent and move logic out of this entity ...
                    // throw new ORM\MappingException('Unknown STATUS in JoinRequest!');
                    $this->status = $pStatus;
                }
            }
        }

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
     * @return JoinRequest
     */
    public function setPayed(bool $payed): self
    {
        $this->payed = $payed;

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
     * @return JoinRequest
     */
    public function setAccepted(bool $accepted): self
    {
        $this->accepted = $accepted;

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
     * @return JoinRequest
     */
    public function setDenied(bool $denied): self
    {
        $this->denied = $denied;

        return $this;
    }
}
