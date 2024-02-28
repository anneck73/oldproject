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
use Knp\DoctrineBehaviors\Model\Tree\Node;
use Knp\DoctrineBehaviors\Model\Tree\NodeInterface;
use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;

/**
 * @ORM\Entity(repositoryClass="Mealmatch\ApiBundle\Repository\Meal\ProMealRepository")
 */
class ProMeal extends BaseMeal implements NodeInterface
{
    use Node;

    /**
     * Unidirectional - one-to-many Many BaseMeals have many MealAddresses (OWNING SIDE!!!).
     *
     * @var Collection
     * @ORM\ManyToMany(targetEntity="MealAddress", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinTable(name="pro_meal_to_meal_address",
     *      joinColumns={@ORM\JoinColumn(name="pro_meal_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="meal_address_id", referencedColumnName="id")}
     *      )
     */
    protected $mealAddresses;

    /**
     * Unidirectional - one-to-many Many ProMeals have many MealAddresses (OWNING SIDE!!!).
     *
     * @var Collection
     * @ORM\ManyToMany(targetEntity="MealOffer", cascade={"persist"})
     * @ORM\JoinTable(name="pro_meal_to_meal_offer",
     *      joinColumns={@ORM\JoinColumn(name="pro_meal_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="meal_offer_id", referencedColumnName="id")}
     *      )
     */
    protected $mealOffers;

    /**
     * @ORM\Column(name="table_topic", type="string", nullable=false)
     *
     * @var string
     */
    private $tableTopic = '-DEFAULT-';

    /**
     * Specials / Besonderheiten.
     *
     * @ORM\Column(name="specials", type="text", nullable=false, length=300)
     *
     * @var string
     */
    private $specials = '-DEFAULT-';

    /**
     * Notes regarding the contained meal offers.
     *
     * @ORM\Column(name="meal_offer_notes", type="text", nullable=false, length=2500)
     *
     * @var string
     */
    private $mealOfferNotes = '-DEFAULT-';

    /**
     * Country specific notes regarding the meal.
     *
     * @ORM\Column(name="country_offer_notes", type="text", nullable=false, length=2500)
     *
     * @var string
     */
    private $countryOfferNotes = '-DEFAULT-';

    /**
     * ProMeal constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->mealAddresses = new ArrayCollection();
        $this->mealOffers = new ArrayCollection();
    }

    /**
     * Returns all MealAddresses for this ProMeal.
     *
     * @return Collection
     */
    public function getMealAddresses(): Collection
    {
        return $this->mealAddresses;
    }

    /**
     * Returns the first MealAddress contained in MealAddresses.
     *
     * @return MealAddress the MealAddress of the ProMeal
     */
    public function getMealAddress(): MealAddress
    {
        if (0 === $this->mealAddresses->count()) {
            return null;
        }

        return $this->mealAddresses->first();
    }

    /**
     * Sets the internal MealAddresses using a Collection of MealAddress entities.
     *
     * @param Collection $mealAddresses
     *
     * @return ProMeal
     */
    public function setMealAddresses(Collection $mealAddresses)
    {
        $this->mealAddresses = $mealAddresses;

        return $this;
    }

    public function addMealAddress(MealAddress $mealAddress): self
    {
        $this->mealAddresses->add($mealAddress);

        return $this;
    }

    public function hasAddress()
    {
        return $this->mealAddresses->count() > 0 ? true : false;
    }

    /**
     * @return string
     */
    public function getTableTopic(): string
    {
        return $this->tableTopic;
    }

    /**
     * @param string $tableTopic
     *
     * @return ProMeal
     */
    public function setTableTopic(string $tableTopic): self
    {
        $this->tableTopic = $tableTopic;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getMealOffers(): Collection
    {
        return $this->mealOffers;
    }

    /**
     * @param Collection $mealOffers
     *
     * @return ProMeal
     */
    public function setMealOffers(Collection $mealOffers)
    {
        $this->mealOffers = $mealOffers;

        return $this;
    }

    public function addMealOffer(MealOffer $mealOffer): self
    {
        $this->mealOffers->add($mealOffer);

        return $this;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param MealOffer $mealOffer
     *
     * @return ProMeal
     */
    public function removeMealOffer(MealOffer $mealOffer): self
    {
        if ($this->mealOffers->contains($mealOffer)) {
            $this->mealOffers->removeElement($mealOffer);
        }

        return $this;
    }

    public function getMinOfferPrice(): float
    {
        $prices = array();
        /** @var MealOffer $mealOffer */
        foreach ($this->mealOffers as $mealOffer) {
            $prices[] = $mealOffer->getPrice();
        }

        return min($prices);
    }

    public function getMaxOfferPrice(): float
    {
        $prices = array();
        /** @var MealOffer $mealOffer */
        foreach ($this->mealOffers as $mealOffer) {
            $prices[] = $mealOffer->getPrice();
        }

        return max($prices);
    }

    /**
     * @return string
     */
    public function getSpecials(): string
    {
        return $this->specials;
    }

    /**
     * @param string $specials
     *
     * @return ProMeal
     */
    public function setSpecials(string $specials): self
    {
        $this->specials = $specials;

        return $this;
    }

    /**
     * @return string
     */
    public function getMealOfferNotes(): string
    {
        return $this->mealOfferNotes;
    }

    /**
     * @param string $mealOfferNotes
     *
     * @return ProMeal
     */
    public function setMealOfferNotes(string $mealOfferNotes): self
    {
        $this->mealOfferNotes = $mealOfferNotes;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountryOfferNotes(): string
    {
        return $this->countryOfferNotes;
    }

    /**
     * @param string $countryOfferNotes
     *
     * @return ProMeal
     */
    public function setCountryOfferNotes(string $countryOfferNotes): self
    {
        $this->countryOfferNotes = $countryOfferNotes;

        return $this;
    }

    // ---------------------------------------------------------------------
    // The methods below are not part of persistence but offer easier
    // access to its values.
    // ---------------------------------------------------------------------

    /**
     * Returns the location of this ProMeal.
     *
     * @return Point the geo coordinates
     */
    public function getLocation(): Point
    {
        return $this->getMealAddress()->getLocation();
    }

    /**
     * Returns the location string of this ProMeal.
     *
     * @return string the location of this ProMeal
     */
    public function getLocationString(): string
    {
        return $this->getMealAddress()->getLocationString();
    }

    /**
     * Helper method to determine the node id to be able to create a tree structure of ProMeals.
     *
     * @return int the node id
     */
    public function getNodeId()
    {
        return $this->getId();
    }
}
