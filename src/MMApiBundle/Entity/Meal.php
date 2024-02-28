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
use MMUserBundle\Entity\MMUser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Meal.
 *
 * @ORM\Table(name="meal")
 * @ORM\Entity(repositoryClass="MMApiBundle\Repository\MealRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Meal
{
    /*
     * TRAITS
     */
    use ORMBehaviors\Sortable\Sortable;
    use
        ORMBehaviors\Timestampable\Timestampable;

    public static $STATUS_CREATED = 'CREATED';
    public static $STATUS_RUNNING = 'RUNNING';
    public static $STATUS_STOPPED = 'STOPPED';
    public static $STATUS_FINISHED = 'FINISHED';
    public static $STATUS_DELETED = 'DELETED';
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
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;
    /**
     * @todo: Finish PHPDoc!
     *
     * @var \DateTime
     * @ORM\Column(name="startDateTime", type="datetime", nullable=true)
     */
    private $startDateTime;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var int
     * @ORM\Column(name="maxGuest", type="integer")
     */
    private $maxNumberOfGuest = 1;
    /**
     * Many Meals have Many Categories.
     *
     * @ORM\ManyToMany(targetEntity="MealCategory")
     * @ORM\JoinTable(name="meal_to_category",
     *      joinColumns={@ORM\JoinColumn(name="meal_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")}
     *      )
     */
    private $categories;
    /**
     * @var string
     *
     * @ORM\Column(name="starter", type="string", length=255, nullable=true)
     */
    private $starter;
    /**
     * @var string
     *
     * @ORM\Column(name="main", type="string", length=255)
     */
    private $main;
    /**
     * @var string
     *
     * @ORM\Column(name="desert", type="string", length=255, nullable=true)
     */
    private $desert;
    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Assert\Length(
     *      max = 500,
     *      maxMessage = "meal.description.length.maximum"
     * )
     */
    private $description;

    /**
     * @var float
     * @ORM\Column(name="sharedCost", type="float")
     */
    private $sharedCost = 0.0;
    /**
     * @var string
     * @ORM\Column(name="sharedCostCurrency", type="string")
     */
    private $sharedCostCurrency;
    /**
     * @var string
     * @ORM\Column(name="locationAddress", type="string")
     */
    private $locationAddress;
    /**
     * One Meal has one Address.
     *
     * @todo: Finish PHPDoc!
     *
     * @var Address
     * @ORM\OneToOne(targetEntity="Address",
     *     mappedBy="meal",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove", "merge"});
     */
    private $address;
    /**
     * @var float
     * @ORM\Column(name="locationLat", type="float", nullable=true)
     */
    private $latitude;
    /**
     * @var float
     * @ORM\Column(name="locationLong", type="float", nullable=true)
     */
    private $longitude;

    /**
     * The Host of the Meal.
     *
     * @var MMUser
     *
     * Many Meals have One Host
     *
     * @ORM\ManyToOne(targetEntity="MMUserBundle\Entity\MMUser", inversedBy="hostingMeals")
     * @ORM\JoinColumn(name="host_id", referencedColumnName="id")
     */
    private $host;
    /**
     * A Meal is connected to tickets as a meal.
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="MMApiBundle\Entity\MealTicket", mappedBy="meal", fetch="EAGER", cascade={"persist","remove"})
     */
    private $mealTickets;
    /**
     * A Meal has many join requests.
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="MMApiBundle\Entity\JoinRequest", mappedBy="meal", cascade={"persist","remove"})
     */
    private $joinRequests;
    /**
     * @todo: Finish PHPDoc!
     *
     * @var string
     * @ORM\Column(name="status", type="string", length=12)
     */
    private $status = 'CREATED';

    /**
     * Meal constructor.
     */
    public function __construct()
    {
        $this->guests = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->mealTickets = new ArrayCollection();
        $this->joinRequests = new ArrayCollection();

        if (null === $this->hash) {
            $tokenG = new TokenGenerator();
            $this->hash = $tokenG->generateToken();
        }
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
    public function __toString()
    {
        return __CLASS__.' ('.$this->getId().')';
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
     * @return Meal
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return MMUser
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param MMUser $host
     *
     * @return Meal
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @param ArrayCollection $guests
     */
    public function setGuests(ArrayCollection $guests)
    {
        $this->guests = $guests;
    }

    /**
     * @return int
     */
    public function getMaxNumberOfGuest()
    {
        return $this->maxNumberOfGuest;
    }

    /**
     * @param int $maxNumberOfGuest
     *
     * @return Meal
     */
    public function setMaxNumberOfGuest($maxNumberOfGuest)
    {
        $this->maxNumberOfGuest = $maxNumberOfGuest;

        return $this;
    }

    /**
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param float $longitude
     *
     * @return Meal
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return string
     */
    public function getSharedCostCurrency()
    {
        return $this->sharedCostCurrency;
    }

    /**
     * @param string $sharedCostCurrency
     *
     * @return Meal
     */
    public function setSharedCostCurrency($sharedCostCurrency)
    {
        $this->sharedCostCurrency = $sharedCostCurrency;

        return $this;
    }

    /**
     * @return float
     */
    public function getSharedCost(): float
    {
        return $this->sharedCost;
    }

    /**
     * @param float
     * @param mixed $sharedCost
     *
     * @return Meal
     */
    public function setSharedCost($sharedCost)
    {
        $this->sharedCost = $sharedCost;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return Meal
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get starter.
     *
     * @return string
     */
    public function getStarter()
    {
        return $this->starter;
    }

    /**
     * Set starter.
     *
     * @param string $starter
     *
     * @return Meal
     */
    public function setStarter($starter)
    {
        $this->starter = $starter;

        return $this;
    }

    /**
     * Get main.
     *
     * @return string
     */
    public function getMain()
    {
        return $this->main;
    }

    /**
     * Set main.
     *
     * @param string $main
     *
     * @return Meal
     */
    public function setMain($main)
    {
        $this->main = $main;

        return $this;
    }

    /**
     * Get desert.
     *
     * @return string
     */
    public function getDesert()
    {
        return $this->desert;
    }

    /**
     * Set desert.
     *
     * @param string $desert
     *
     * @return Meal
     */
    public function setDesert($desert)
    {
        $this->desert = $desert;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Meal
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMealTickets()
    {
        return $this->mealTickets;
    }

    /**
     * @param mixed $mealTickets
     *
     * @return Meal
     */
    public function setMealTickets($mealTickets)
    {
        $this->mealTickets = $mealTickets;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartDateTime(): \DateTime
    {
        if (null === $this->startDateTime) {
            $this->startDateTime = new \DateTime('now');
        }

        return $this->startDateTime;
    }

    /**
     * @param \DateTime $startDateTime
     *
     * @return Meal
     */
    public function setStartDateTime(\DateTime $startDateTime): self
    {
        $this->startDateTime = $startDateTime;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getJoinRequests()
    {
        return $this->joinRequests;
    }

    /**
     * @param ArrayCollection $joinRequests
     *
     * @return Meal
     */
    public function setJoinRequests(ArrayCollection $joinRequests)
    {
        $this->joinRequests = $joinRequests;

        return $this;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param JoinRequest $joinRequest
     */
    public function addJoinRequest(JoinRequest $joinRequest)
    {
        $joinRequest->setMeal($this);
        $this->joinRequests->add($joinRequest);
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
     * @return
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param ArrayCollection $categories
     *
     * @return Meal
     */
    public function setCategories(ArrayCollection $categories): self
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param \stdClass $category
     * @param string    $myArgument with a *description* of this argument, these may also
     *                              span multiple lines
     *
     * @return mixed
     */
    public function setCategory($category)
    {
        // hmmmm...
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
     * @return mixed
     */
    public function getCategory()
    {
        // TODO: Implement getCategory() method.
    }

    public function hasAddress(): bool
    {
        return null !== $this->address;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     *
     * @return Meal
     */
    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function toJSON()
    {
    }

    /**
     * @todo: Finish PHPDoc!
     *
     * @return bool
     */
    public function isValid(): bool
    {
        $retValue = false;
        if ((null !== $this->getLocationAddress()
                && '-' !== $this->getLocationAddress())
            && null !== $this->getLatitude()
            && null !== $this->getAddress()
            && null !== $this->getAddress()->getPostalCode()
            && $this->getAddress()->getLocationAddress() !== $this->getLocationAddress()
        ) {
            $retValue = true;
        }

        return $retValue;
    }

    /**
     * @return string
     */
    public function getLocationAddress()
    {
        return $this->locationAddress;
    }

    /**
     * @param string $locationAddress
     *
     * @return Meal
     */
    public function setLocationAddress($locationAddress)
    {
        $this->locationAddress = $locationAddress;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param mixed $latitude
     *
     * @return Meal
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param Address $address
     *
     * @return Meal
     */
    public function setAddress(Address $address): self
    {
        $this->address = $address;

        return $this;
    }
}
