<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Entity\Meal;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Blameable\Blameable;
use Knp\DoctrineBehaviors\Model\Timestampable\Timestampable;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Entity\AbstractEntity;
use Mealmatch\ApiBundle\MealMatch\Traits\Hashable;
use MMUserBundle\Entity\MMUser;
use ReflectionClass;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * All Meal's are represented through table inheritance of BaseMeal variations.
 * All Meal's are part of a Tree, with a unique root-Meal and many unique leave-Meal's.
 * Root-Meal's exist as starting "templates" during the creation of leave-Meal's, they are NOT USED for payment,
 * joinrequest's, or any other user interaktion but Batch processing. (not implemented yet)
 * The root-Meal is "visible" to the user only inside a MealManager.
 *
 * @ORM\Entity(repositoryClass="Mealmatch\ApiBundle\Repository\Meal\BaseMealRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="meal_type", type="string")
 * @ORM\DiscriminatorMap({
 *     "base" = "BaseMeal",
 *     "pro" = "ProMeal",
 *     "home" = "HomeMeal"
 *          }
 *     )
 */
class BaseMeal extends AbstractEntity
{
    /*
     * TRAITS
     */
    use Timestampable;
    use
        Blameable;
    use
        Hashable;

    /**
     * The unique ID of the Meal.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Meal Properties.
     *
     * @var string
     * @ORM\Column(type="json_array")
     */
    protected $properties = array();

    /**
     * Meal Permissions.
     *
     * @var string
     * @ORM\Column(type="json_array")
     */
    protected $permissions = array();

    /**
     * The "title" of the meal.
     *
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title;

    /**
     * All time specific dimensions of the meal. Start, End, Re-Occuring, etc.
     *
     * Unidirectional - one-to-many Many BaseMeals have many MealAddresses (OWNING SIDE!!!).
     *
     * @var Collection
     *
     * @Assert\NotBlank()
     *
     * @ORM\ManyToMany(targetEntity="Mealmatch\ApiBundle\Entity\Meal\MealEvent", cascade={"persist"})
     * @ORM\JoinTable(name="base_meal_to_meal_event",
     *      joinColumns={@ORM\JoinColumn(name="base_meal_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="meal_event_id", referencedColumnName="id")}
     *      )
     */
    protected $mealEvents;

    /**
     * Coupons are connected to MealCoupons.
     *
     * Unidirectional - one-to-many Many BaseMeals have many MealCoupons (OWNING SIDE!!!).
     *
     * @var Collection
     *
     * @Assert\NotBlank()
     *
     * @ORM\ManyToMany(targetEntity="Mealmatch\ApiBundle\Entity\Coupon\MealCoupon", cascade={"persist"})
     * @ORM\JoinTable(name="base_meal_to_meal_coupon",
     *      joinColumns={@ORM\JoinColumn(name="base_meal_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="meal_coupon_id", referencedColumnName="id")}
     *      )
     */
    protected $mealCoupons;

    /**
     * The maximum number of allowed guest into this meal.
     *
     * @var int the maximum name of guest
     * @ORM\Column(name="maxGuest", type="integer")
     */
    protected $maxNumberOfGuest = 1;

    /**
     * A detailed description of the meal.
     *
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * The shared costs of this meal.
     *
     * @var float
     * @ORM\Column(name="sharedCost", type="float")
     */
    protected $sharedCost = 0.00;

    /**
     * The currency of the shared costs, eg. EUR, US, CHF, etc ...
     *
     * @var string
     * @ORM\Column(name="sharedCostCurrency", type="string", length=3)
     */
    protected $sharedCostCurrency = 'EUR';

    /**
     * Meals have Users as guests.
     *
     * @var Collection
     * @ORM\ManyToMany(targetEntity="MMUserBundle\Entity\MMUser", mappedBy="attendingBaseMeals")
     */
    protected $guests;

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
    protected $host;

    /**
     * A BaseMeal is connected to MealTickets.
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket",
     *     mappedBy="baseMeal",
     *     cascade={"persist","remove"})
     */
    protected $mealTickets;

    /**
     * A Meal has many join requests.
     *
     * @var ArrayCollection
     * @ORM\OneToMany(
     *     targetEntity="Mealmatch\ApiBundle\Entity\Meal\MealJoinRequest",
     *     mappedBy="baseMeal",
     *     cascade={"persist","remove"}
     *     )
     */
    protected $joinRequests;

    /**
     * Bidirectional - Many BaseMeals are in many BaseMealCatgories (OWNING SIDE!!!).
     *
     * @ORM\ManyToMany(targetEntity="Mealmatch\ApiBundle\Entity\Meal\BaseMealCategory", inversedBy="baseMeal")
     * @ORM\JoinTable(name="base_meal_to_category")
     */
    protected $categories;

    /**
     * The country type of this meal.
     *
     * @var string
     * @ORM\Column(name="country_category", type="string", nullable=true)
     */
    protected $countryCategory;

    /**
     * The status of the meal.
     *
     * @var array the status of the meal
     * @ORM\Column(name="status", type="string")
     */
    protected $status = ApiConstants::MEAL_STATUS_CREATED;

    /**
     * Returns true if this meal is a child of a root-Meal.
     *
     * @var bool true if this meal is a child of a root-Meal
     * @ORM\Column(name="leaf", type="boolean", nullable=true)
     */
    protected $leaf;

    /**
     * BaseMeal constructor.
     */
    public function __construct()
    {
        // initHash if not hashed yet ...
        if (!$this->isHashed()) {
            $this->initHash();
        }
        $this->guests = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->mealTickets = new ArrayCollection();
        $this->joinRequests = new ArrayCollection();
        $this->mealEvents = new ArrayCollection();
        $this->mealCoupons = new ArrayCollection();
        $this->setCreatedAt(new DateTime('now'));
    }

    public function __toString()
    {
        return $this->getTitle().'#'.$this->getId();
    }

    public function getCity()
    {
        if (null !== $this->getAddress()) {
            if (!\is_array($this->getAddress())) {
                return $this->getAddress()->getCity();
            }
        }

        return null;
    }

    public function getCountryCode()
    {
        if ((null !== $this->getAddress()) && !\is_array($this->getAddress())) {
            return $this->getAddress()->getCountryCode();
        }

        return 'de';
    }

    public function getCountry()
    {
        if (null !== $this->getAddress()) {
            if (!\is_array($this->getAddress())) {
                return $this->getAddress()->getCountry();
            }
        }

        return 'germany';
    }

    /**
     * @return bool
     */
    public function isLeaf(): bool
    {
        if (null === $this->leaf) {
            $this->leaf = false;

            return $this->leaf;
        }

        return $this->leaf;
    }

    /**
     * @param bool $leaf
     *
     * @return BaseMeal
     */
    public function setLeaf(bool $leaf): self
    {
        $this->leaf = $leaf;

        return $this;
    }

    /**
     * @todo: This is just a helper to access MealAddress using getAddress();
     * @todo: Find a better solution!
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return MealAddress
     */
    public function getAddress(): MealAddress
    {
        if ($this instanceof ProMeal) {
            return $this->getMealAddress();
        }
        if ($this instanceof HomeMeal) {
            return $this->getMealAddress();
        }
    }

    /**
     * Returns the ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return BaseMeal
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Returns the start of the meal.
     * Returns the startdatetime from the first meal event.
     *
     * @return DateTime|null
     */
    public function getStartDateTime()
    {
        if ($this->mealEvents->count() > 0) {
            return $this->mealEvents->first()->getStartDateTime();
        }

        return null;
    }

    /**
     * Returns the start of the meal.
     * Returns the startdatetime from the first meal event.
     *
     * @return DateTime|null
     */
    public function getEndDateTime()
    {
        if ($this->mealEvents->count() > 0) {
            return $this->mealEvents->first()->getEndDateTime();
        }

        return null;
    }

    /**
     * Sets the start of the meal.
     * Sets the startdatetime of the first meal event.
     *
     * @param DateTime $startDateTime
     *
     * @return BaseMeal
     */
    public function setStartDateTime(DateTime $startDateTime)
    {
        if (null === $startDateTime) {
            return $this;
        }
        //@todo... this is BAD! cause 0 elements in mealEvents will cause an ERROR
        // move this logic into the service layer, somehow!
        // IMPORTANT! ONLY PHPUNIT TESTS SHOULD CAUSE THIS ERROR! USE THEM!
        /** @var MealEvent $mealEvent */
        $mealEvent = $this->mealEvents->first();
        $mealEvent->setStartDateTime($startDateTime);

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxNumberOfGuest(): int
    {
        return $this->maxNumberOfGuest;
    }

    /**
     * @param int $maxNumberOfGuest
     *
     * @return BaseMeal
     */
    public function setMaxNumberOfGuest(int $maxNumberOfGuest)
    {
        $this->maxNumberOfGuest = $maxNumberOfGuest;

        return $this;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     *
     * @return BaseMeal
     */
    public function setDescription($description): self
    {
        $this->description = $description;

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
     * @param float $sharedCost
     *
     * @return BaseMeal
     */
    public function setSharedCost(float $sharedCost)
    {
        $this->sharedCost = $sharedCost;

        return $this;
    }

    /**
     * @return string
     */
    public function getSharedCostCurrency(): string
    {
        return $this->sharedCostCurrency;
    }

    /**
     * @param mixed $sharedCostCurrency
     *
     * @return BaseMeal
     */
    public function setSharedCostCurrency($sharedCostCurrency)
    {
        $this->sharedCostCurrency = $sharedCostCurrency;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getGuests(): Collection
    {
        return $this->guests;
    }

    /**
     * @param Collection $guests
     *
     * @return BaseMeal
     */
    public function setGuests(Collection $guests): self
    {
        $this->guests = $guests;

        return $this;
    }

    /**
     * Checks if the MMuser is a guest of this meal.
     *
     * @param MMUser $pUser the user to search in the guestlist
     *
     * @return bool if MMuser is a guest of this meal
     */
    public function isGuest(MMUser $pUser): bool
    {
        return $this->guests->contains($pUser);
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
     * @return BaseMeal
     */
    public function setHost(MMUser $host): self
    {
        $this->host = $host;

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
     * @param ArrayCollection $mealTickets
     *
     * @return BaseMeal
     */
    public function setMealTickets(ArrayCollection $mealTickets): self
    {
        $this->mealTickets = $mealTickets;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getJoinRequests(): Collection
    {
        return $this->joinRequests;
    }

    /**
     * @param ArrayCollection $joinRequests
     *
     * @return BaseMeal
     */
    public function setJoinRequests(ArrayCollection $joinRequests): self
    {
        $this->joinRequests = $joinRequests;

        return $this;
    }

    public function addJoinRequest(MealJoinRequest $joinRequest): self
    {
        $joinRequest->setBaseMeal($this);
        $this->joinRequests->add($joinRequest);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     *
     * @return BaseMeal
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Checks if the status is CREATED.
     *
     * @return bool true if status is CREATED
     */
    public function isCreated(): bool
    {
        return ApiConstants::MEAL_STATUS_CREATED === $this->status ? true : false;
    }

    /**
     * Checks if the status is READY.
     *
     * @return bool true if status is READY
     */
    public function isReady(): bool
    {
        return ApiConstants::MEAL_STATUS_READY === $this->status ? true : false;
    }

    /**
     * Returns all BaseMealCategories.
     *
     * @return ArrayCollection the collection of BaseMealCategory objects
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Sets all categories of this meal.
     *
     * @param Collection $categories the BaseMealCategory collection to use
     *
     * @return $this the updated meal
     */
    public function setCategories(Collection $categories)
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * Adds a BaseMealCategory to this meal.
     *
     * @param BaseMealCategory $baseMealCategory
     *
     * @return BaseMeal the updated meal
     */
    public function addCategory(BaseMealCategory $baseMealCategory): self
    {
        $this->categories->add($baseMealCategory);

        return $this;
    }

    /**
     * Returns the country category of this meal.
     *
     * @return string|null the country category
     */
    public function getCountryCategory()
    {
        return $this->countryCategory;
    }

    /**
     * Sets the country category of this meal.
     *
     * @param string $countryCategory
     *
     * @return BaseMeal the updated meal
     */
    public function setCountryCategory(string $countryCategory): self
    {
        $this->countryCategory = $countryCategory;

        return $this;
    }

    /**
     * Add the given MMUser as a guest to this Meal.
     *
     * @param MMUser $guestUser the MMUser to add
     *
     * @return BaseMeal the updated Meal
     */
    public function addGuest(MMUser $guestUser): self
    {
        if ($this->guests->contains($guestUser)) {
            // user already in guests.
            return $this;
        }

        $guestUser->addAttendingBaseMeal($this);
        $this->guests->add($guestUser);

        return $this;
    }

    /**
     * Returns the current collection of mealEvents.
     *
     * @return Collection the current mealEvents
     */
    public function getMealEvents(): Collection
    {
        return $this->mealEvents;
    }

    /**
     * Returns the first MealEvent of MealEvents or null.
     *
     * @return MealEvent|null
     */
    public function getMealEvent()
    {
        if (0 === $this->mealEvents->count()) {
            return null;
        }

        return $this->mealEvents->first();
    }

    public function setMealCoupons(Collection $mealCoupons): self
    {
        $this->mealCoupons = $mealCoupons;

        return $this;
    }

    /**
     * Set's all mealEvents.
     *
     * @param Collection $mealEvents the new collection of mealEvents
     *
     * @return BaseMeal
     */
    public function setMealEvents(Collection $mealEvents): self
    {
        $this->mealEvents = $mealEvents;

        return $this;
    }

    /**
     * Add's one MealEvent ot the current list of mealEvents.
     *
     * @param MealEvent $mealEvent the MealEvent to add
     *
     * @return BaseMeal
     */
    public function addMealEvent(MealEvent $mealEvent): self
    {
        $this->mealEvents->add($mealEvent);

        return $this;
    }

    /**
     * Removes a MealEvent from the MealEvents of this Meal.
     *
     * @param MealEvent $mealEvent the MealEvent to remove
     *
     * @return BaseMeal the updated BaseMeal
     */
    public function removeMealEvent(MealEvent $mealEvent): self
    {
        $this->mealEvents->removeElement($mealEvent);

        return $this;
    }

    /**
     * Returns the Username or null.
     *
     * @return string|null
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Returns the Username or null.
     *
     * @return string|null
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Returns the Username or null.
     *
     * @return string|null
     */
    public function getDeletedBy()
    {
        return $this->deletedBy;
    }

    /**
     * Reflection helper to get the ShortName of $this;
     * Possible return values as of 0.2.5: 'BaseMeal', 'HomeMeal', 'ProMeal'.
     *
     * @return string the ShortName of $this
     */
    public function getMealType(): string
    {
        try {
            return (new ReflectionClass($this))->getShortName();
        } catch (\ReflectionException $e) {
            // this would be bad!
        }
    }

    /**
     * Helper to get all properties of $this as an associative array.
     * Using $this as $key => $value.
     *
     * @return array the properties of $this
     */
    private function toArray()
    {
        $props = array();
        foreach ((array) $this as $key => $value) {
            $props[$key] = $value;
        }

        return $props;
    }
}
