<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Entity\Meal;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Entity\AbstractEntity;
use Mealmatch\ApiBundle\Entity\Coupon\RedeemRequest;
use Mealmatch\ApiBundle\Entity\Coupon\UsedCoupon;
use MMUserBundle\Entity\MMUser;

/**
 * The MealTicket Entity stores all sales informations about a sale.
 *
 * @ORM\Entity(repositoryClass="Mealmatch\ApiBundle\Repository\Meal\BaseMealTicketRepository")
 */
class BaseMealTicket extends AbstractEntity implements MealData
{
    /**
     * The Status of the Ticket indicating its position in the MealTicket workflow.
     * Created -> The MealTicket entity has been created.
     *
     *
     * @var string
     * @ORM\Column(name="status", type="string")
     */
    private $status = ApiConstants::MEAL_TICKET_STATUS_CREATED;

    /**
     * The last payment status of the Ticket containing the result of the last mangopay hook processed.
     *
     * @var string
     * @ORM\Column(name="last_payment_status", type="string")
     */
    private $lastPaymentStatus = '-NONE-';

    /**
     * Ticket condition indicating if all payment relevant details are available before starting the payment process
     * with mangopay.
     *
     * @var bool true if ticket can be handed over to payment processing, false if not
     * @ORM\Column(name="payable", type="boolean")
     */
    private $payable = false;

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
     * Many MealTickets have one BaseMeal.
     *
     * @ORM\ManyToOne(targetEntity="Mealmatch\ApiBundle\Entity\Meal\BaseMeal",
     *     inversedBy="mealTickets",
     *     cascade={"persist"})
     * @ORM\JoinColumn(name="Ticket_BaseMeal_id", referencedColumnName="id", unique=false)
     */
    private $baseMeal;

    /**
     * One MealTicket has one selected MealOffer.
     *
     * @ORM\ManyToOne(targetEntity="Mealmatch\ApiBundle\Entity\Meal\MealOffer", inversedBy="mealTickets")
     * @ORM\JoinColumn(name="Ticket_Offer_id", referencedColumnName="id", unique=false)
     */
    private $selectedMealOffer;

    /**
     * The price of this MealTicket.
     *
     * @var float
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    /**
     * The currency associated to the price.
     *
     * @var string
     * @ORM\Column(name="currency", type="string", length=5)
     */
    private $currency;

    /**
     * The mealmatch fee if any.
     *
     * @var float
     * @ORM\Column(name="mmFee", type="float")
     */
    private $mmFee;

    /**
     * One MealTicket potentially has a coupon code associated with it.
     *
     * @ORM\ManyToOne(targetEntity="Mealmatch\ApiBundle\Entity\Coupon\UsedCoupon", inversedBy="mealTickets")
     * @ORM\JoinColumn(name="Coupon_id", referencedColumnName="id", unique=false)
     */
    private $coupon;

    /**
     * The number of tickets refers to the count of "guest places" at the meal
     * in case someone wants to "pay" for more than 1 seat at the table.
     *
     * @var int
     */
    private $numberOfTickets = 1;

    /**
     * The titel of this MealTicket.
     *
     * @var string ;
     * @ORM\Column(name="titel", type="string")
     */
    private $titel;

    /**
     * The description of the meal associated to this MealTicket.
     *
     * @var string
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * One MealTicket has many redeem requests.
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Mealmatch\ApiBundle\Entity\Coupon\RedeemRequest",
     *     mappedBy="mealTicket", cascade={"persist"})
     */
    private $redeemRequests;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="Mealmatch\ApiBundle\Entity\Meal\MealTicketTransaction",
     *     mappedBy="mealTicket")
     */
    private $transactions;

    /**
     * The MealTicket number.
     *
     * @var string
     * @ORM\Column(name="number", type="string")
     */
    private $number = '#MM#';

    /**
     * MealTicket constructor.
     */
    private $redirectURL;

    /**
     * Mangopay PayIn Status.
     *
     * @var string
     * @ORM\Column(name="pay_in_status", type="string", length=128, nullable=true)
     */
    private $payInStatus;

    /**
     * Type of payment. Defaults to CARD.
     *
     * @var string
     * @ORM\Column(name="paymentType", type="string")
     */
    private $paymentType;

    /**
     * @todo: If I understand the usage of this member correctly, it contains the resourceId of the Wallet of the Guest when
     *     doing the payout into the host wallet. This data should be available already, maybe this can be removed.
     *
     * @var string
     * @ORM\Column(name="resource_id", type="string", length=128, nullable=true)
     */
    private $resourceId;

    public function __construct()
    {
        parent::__construct();
        $this->redeemRequests = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->paymentType = 'CARD';
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
        return 'BaseMealTicket'.$this->getId();
    }

    /**
     * @return string|null
     */
    public function getResourceId(): ?string
    {
        if ($this->transactions->count() > 0) {
            /** @var MealTicketTransaction $lastTransaction */
            $lastTransaction = $this->transactions->last();
            // @todo: last should be by date!
            return $lastTransaction->getResourceId();
        }

        return $this->resourceId;
    }

    /**
     * @param string $resourceId
     *
     * @return BaseMealTicket
     */
    public function setResourceId(string $resourceId): self
    {
        $this->resourceId = $resourceId;

        return $this;
    }

    /**
     * @return int
     */
    public function getNumberOfTickets(): int
    {
        return $this->numberOfTickets;
    }

    /**
     * @param int $numberOfTickets
     *
     * @return BaseMealTicket
     */
    public function setNumberOfTickets(int $numberOfTickets): self
    {
        $this->numberOfTickets = $numberOfTickets;

        return $this;
    }

    /**
     * @return BaseMeal|null
     */
    public function getBaseMeal()
    {
        return $this->baseMeal;
    }

    /**
     * A simple proxy method, same as getBaseMeal.
     *
     * @return mixed
     */
    public function getMeal()
    {
        return $this->baseMeal;
    }

    /**
     * @param BaseMeal $baseMeal
     *
     * @return BaseMealTicket
     */
    public function setBaseMeal(BaseMeal $baseMeal)
    {
        $this->baseMeal = $baseMeal;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitel()
    {
        return $this->titel;
    }

    /**
     * @param string $titel
     *
     * @return BaseMealTicket
     */
    public function setTitel(string $titel): self
    {
        $this->titel = $titel;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return BaseMealTicket
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     *
     * @return BaseMealTicket
     */
    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getJson()
    {
        $jsonData = array(
            'ID' => $this->getId(),
            'Status' => $this->getStatus(),
            'Transactions' => $this->getTransactions()->count(),
            'Meal' => $this->getBaseMeal()->getTitle(),
            'TicketHost' => $this->getHost()->getUsername(),
            'TicketGuest' => $this->getGuest()->getUsername(),
            'TicketPrice' => $this->getPrice(),
            'TicketCurrency' => $this->getCurrency(),
            'TicketFee' => $this->getMmFee(),
        );

        return json_encode($jsonData);
    }

    /**
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return BaseMealTicket
     */
    public function setStatus($status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Returns the MMUser who is the Host of the meal for this ticket.
     *
     * @return MMUser
     */
    public function getHost(): MMUser
    {
        return $this->host;
    }

    /**
     * @param MMUser $host
     *
     * @return BaseMealTicket
     */
    public function setHost(MMUser $host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return MMUser
     */
    public function getGuest(): MMUser
    {
        return $this->guest;
    }

    /**
     * @param MMUser $guest
     *
     * @return BaseMealTicket
     */
    public function setGuest(MMUser $guest)
    {
        $this->guest = $guest;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getMmFee()
    {
        return $this->mmFee;
    }

    /**
     * @param float $mmFee
     *
     * @return BaseMealTicket
     */
    public function setMmFee(float $mmFee): self
    {
        $this->mmFee = $mmFee;

        return $this;
    }

    /**
     * The original ticket price in cent.
     *
     * @return int
     */
    public function getPriceInCent(): int
    {
        return $this->getPrice() * 100;
    }

    /**
     * The total ticket price including coupon.
     *
     * @return float
     */
    public function getTotalPrice(): float
    {
        if (null !== $this->getCoupon()) {
            return $this->price - $this->getCoupon()->getValue();
        }

        return $this->price;
    }

    /**
     * The total ticket price in cents.
     */
    public function getTotalPriceInCent(): int
    {
        return $this->getTotalPrice() * 100;
    }

    /**
     * The original ticket price.
     *
     * @return float|null
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     *
     * @return BaseMealTicket
     */
    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return BaseMealTicket
     */
    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return UsedCoupon
     */
    public function getCoupon(): ?UsedCoupon
    {
        return $this->coupon;
    }

    /**
     * @param UsedCoupon|null $coupon
     *
     * @return BaseMealTicket
     */
    public function setCoupon(?UsedCoupon $coupon): self
    {
        $this->coupon = $coupon;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSelectedMealOffer()
    {
        return $this->selectedMealOffer;
    }

    /**
     * @param mixed $selectedMealOffer
     *
     * @return BaseMealTicket
     */
    public function setSelectedMealOffer($selectedMealOffer)
    {
        $this->selectedMealOffer = $selectedMealOffer;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @return string|null
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return mixed
     */
    public function getRedirectURL()
    {
        return $this->redirectURL;
    }

    /**
     * @param mixed $redirectURL
     */
    public function setRedirectURL($redirectURL): self
    {
        $this->redirectURL = $redirectURL;

        return $this;
    }

    /**
     * The mangopay payin status.
     *
     * @return string|null
     */
    public function getPayInStatus(): ?string
    {
        return $this->payInStatus;
    }

    /**
     * @param string $payInStatus
     *
     * @return BaseMealTicket
     */
    public function setPayInStatus(string $payInStatus): self
    {
        $this->payInStatus = $payInStatus;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * @param string $paymentType
     *
     * @return BaseMealTicket
     */
    public function setPaymentType(string $paymentType): self
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getRedeemRequests(): Collection
    {
        return $this->redeemRequests;
    }

    /**
     * @param RedeemRequest $redeemRequest
     *
     * @return BaseMealTicket
     */
    public function addRedeemRequest(RedeemRequest $redeemRequest): self
    {
        $this->redeemRequests->add($redeemRequest);

        return $this;
    }

    /**
     * Returns all Mangopay transactions for the Mealticket.
     *
     * @return Collection
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    /**
     * @param Collection $transactions
     *
     * @return BaseMealTicket
     */
    public function setTransactions(Collection $transactions): self
    {
        $this->transactions = $transactions;

        return $this;
    }

    /**
     * Adding a Mangopay transaction relevant for this Mealticket.
     *
     * @param MealTicketTransaction $transaction
     *
     * @return BaseMealTicket
     */
    public function addTransaction(MealTicketTransaction $transaction): self
    {
        $this->transactions->add($transaction);

        return $this;
    }

    /**
     * True if the MealTicket has all required data to execute the payment process with mangopay.
     *
     * @return bool
     */
    public function isPayable(): bool
    {
        return $this->payable;
    }

    /**
     * Set the payable indicator.
     *
     * @param bool $payable
     *
     * @return BaseMealTicket
     */
    public function setPayable(bool $payable): self
    {
        $this->payable = $payable;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastPaymentStatus(): string
    {
        return $this->lastPaymentStatus;
    }

    /**
     * @param string $lastPaymentStatus
     *
     * @return BaseMealTicket
     */
    public function setLastPaymentStatus(string $lastPaymentStatus): self
    {
        $this->lastPaymentStatus = $lastPaymentStatus;

        return $this;
    }
}
