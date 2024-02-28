<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMApiBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Util\TokenGenerator;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Mealmatch\PayPalBundle\MealTicketStatusValues;
use MMUserBundle\Entity\MMUser;

/**
 * The MealTicket Entity stores all sales informations about a sale.
 * Since Mealmatch is selling seats on the table of a host we are
 * selling tickets for meals. A MealTicket is a single guest payment
 * for a specific meal and the shared costs are paid to the host of the meal.
 *
 * @deprecated
 * @ORM\Table(name="meal_ticket")
 * @ORM\Entity(repositoryClass="MMApiBundle\Repository\MealTicketRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class MealTicket
{
    /*
     * TRAITS
     */
    use ORMBehaviors\Blameable\Blameable;
    use
        ORMBehaviors\Geocodable\Geocodable;
    use
        ORMBehaviors\Sortable\Sortable;
    use
        ORMBehaviors\Timestampable\Timestampable;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="hash", type="string", length=255)
     */
    private $hash;

    /**
     * The Status of the Ticket:
     * created|cancelled|pending|sold.
     *
     * @var string
     * @ORM\Column(name="status", type="string")
     */
    private $status;

    /**
     * One MealTicket has One Host (User).
     *
     * @ORM\ManyToOne(targetEntity="MMUserBundle\Entity\MMUser", inversedBy="hostTickets")
     * @ORM\JoinColumn(name="Ticket_Host_id", referencedColumnName="id", unique=false)
     */
    private $host;

    /**
     * One MealTicket has One Guest (User).
     *
     * @ORM\ManyToOne(targetEntity="MMUserBundle\Entity\MMUser", inversedBy="guestTickets")
     * @ORM\JoinColumn(name="Ticket_Guest_id", referencedColumnName="id", unique=false)
     */
    private $guest;

    /**
     * One MealTicket has One Meal (Meal).
     *
     * @ORM\ManyToOne(targetEntity="MMApiBundle\Entity\Meal", inversedBy="mealTickets", cascade={"persist"})
     * @ORM\JoinColumn(name="Ticket_Meal_id", referencedColumnName="id", unique=false)
     */
    private $meal;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var float
     * @ORM\Column(name="sharedCosts", type="float")
     */
    private $sharedCosts;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var float
     * @ORM\Column(name="mmFee", type="float")
     */
    private $mmFee;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var string ;
     * @ORM\Column(name="titel", type="string")
     */
    private $titel;
    /**
     * @todo: Finish PHPDoc!
     *
     * @var string ;
     * @ORM\Column(name="description", type="string")
     */
    private $description;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var string
     * @ORM\Column(name="number", type="string")
     */
    private $number = '#MM#';

    public function __construct()
    {
        // Initial Status of every ticket ...
        $this->status = MealTicketStatusValues::CREATED;
        if (null === $this->hash) {
            $tokenG = new TokenGenerator();
            $this->hash = $tokenG->generateToken();
        }

        $this->paymentTokens = new ArrayCollection();
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
     */
    public function __toString()
    {
        return __CLASS__.$this->getId().$this->getJson();
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @return string
     */
    public function getTitel(): string
    {
        return $this->titel;
    }

    /**
     * @param string $titel
     *
     * @return MealTicket
     */
    public function setTitel(string $titel): self
    {
        $this->titel = $titel;

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
     * @return MealTicket
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @param string $number
     *
     * @return MealTicket
     */
    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function getJson()
    {
        $jsonData = array(
            'ID' => $this->getId(),
            'Status' => $this->getStatus(),
            'Meal' => $this->getMeal()->getTitle(),
            'TicketHost' => $this->getHost()->getUsername(),
            'TicketGuest' => $this->getGuest()->getUsername(),
            'TicketPrice' => $this->getSharedCosts(),
            'TicketFee' => $this->getMmFee(),
        );

        return json_encode($jsonData);
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
     * @return MealTicket
     */
    public function setStatus($status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Meal
     */
    public function getMeal()
    {
        return $this->meal;
    }

    /**
     * @param mixed $meal
     *
     * @return MealTicket
     */
    public function setMeal($meal)
    {
        $this->meal = $meal;

        return $this;
    }

    /**
     * Returns the MMUser who is the Host of the meal for this ticket.
     *
     * @return MMUser
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param mixed $host
     *
     * @return MealTicket
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return MMUser
     */
    public function getGuest()
    {
        return $this->guest;
    }

    /**
     * @param mixed $guest
     *
     * @return MealTicket
     */
    public function setGuest($guest)
    {
        $this->guest = $guest;

        return $this;
    }

    /**
     * @return float
     */
    public function getSharedCosts(): float
    {
        return $this->sharedCosts;
    }

    /**
     * @param float $sharedCosts
     *
     * @return MealTicket
     */
    public function setSharedCosts(float $sharedCosts): self
    {
        $this->sharedCosts = $sharedCosts;

        return $this;
    }

    /**
     * @return float
     */
    public function getMmFee(): float
    {
        return $this->mmFee;
    }

    /**
     * @param float $mmFee
     *
     * @return MealTicket
     */
    public function setMmFee(float $mmFee): self
    {
        $this->mmFee = $mmFee;

        return $this;
    }
}
