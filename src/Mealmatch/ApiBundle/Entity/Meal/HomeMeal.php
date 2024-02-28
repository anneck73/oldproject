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
 * @ORM\Entity(repositoryClass="Mealmatch\ApiBundle\Repository\Meal\HomeMealRepository")
 */
class HomeMeal extends BaseMeal implements NodeInterface
{
    use Node;

    /**
     * Unidirectional - one-to-many Many HomeMeals have many MealAddresses (OWNING SIDE!!!).
     *
     * @var Collection
     * @ORM\ManyToMany(targetEntity="MealAddress", cascade={"persist"})
     * @ORM\JoinTable(name="home_meal_to_meal_address",
     *      joinColumns={@ORM\JoinColumn(name="home_meal_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="meal_address_id", referencedColumnName="id")}
     *      )
     */
    protected $mealAddresses;

    /**
     * Unidirectional - one-to-many Many HomeMeals have many MealParts (OWNING SIDE!!!).
     *
     * @var Collection
     * @ORM\ManyToMany(targetEntity="MealPart", cascade={"persist"})
     * @ORM\JoinTable(name="home_meal_to_meal_part",
     *      joinColumns={@ORM\JoinColumn(name="home_meal_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="meal_part_id", referencedColumnName="id")}
     *      )
     */
    protected $mealParts;

    /**
     * @var string
     * @ORM\Column(name="meal_starter", type="string", nullable=true)
     */
    protected $mealStarter;

    /**
     * @var string
     * @ORM\Column(name="meal_main", type="string")
     */
    protected $mealMain;

    /**
     * @var string
     * @ORM\Column(name="meal_desert", type="string", nullable=true)
     */
    protected $mealDesert;

    public function __construct()
    {
        parent::__construct();
        $this->mealAddresses = new ArrayCollection();
        $this->mealParts = new ArrayCollection();
    }

    /**
     * Returns all MealPart's of this HomeMeal.
     *
     * @return Collection a collection of MealPart's
     */
    public function getMealParts(): Collection
    {
        return $this->mealParts;
    }

    /**
     * @param mixed $mealParts
     *
     * @return HomeMeal
     */
    public function setMealParts(Collection $mealParts)
    {
        $this->mealParts = $mealParts;

        return $this;
    }

    /**
     * Adds a single MealPart.
     *
     * @param MealPart $mealPart the MealPart entity to add
     *
     * @return HomeMeal $this
     */
    public function addMealPart(MealPart $mealPart): self
    {
        $this->mealParts->add($mealPart);

        return $this;
    }

    public function getMealAddresses(): Collection
    {
        return $this->mealAddresses;
    }

    /**
     * Returns the first MealAddress contained in MealAddresses.
     *
     * @return MealAddress|null the MealAddress of the HomeMeal
     */
    public function getMealAddress()
    {
        if (0 === $this->mealAddresses->count()) {
            return null;
        }

        return $this->mealAddresses->first();
    }

    /**
     * @param Collection $mealAddresses
     *
     * @return HomeMeal $this
     */
    public function setMealAddresses(Collection $mealAddresses)
    {
        $this->mealAddresses = $mealAddresses;

        return $this;
    }

    /**
     * Adds one MealAddress to the HomeMeal.
     *
     * @param MealAddress $mealAddress the mealAddress to add
     *
     * @return HomeMeal $this
     */
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
     * @return mixed
     */
    public function getMealStarter()
    {
        return $this->mealStarter;
    }

    /**
     * @param mixed $mealStarter
     *
     * @return HomeMeal
     */
    public function setMealStarter($mealStarter)
    {
        $this->mealStarter = $mealStarter;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMealMain()
    {
        return $this->mealMain;
    }

    /**
     * @param mixed $mealMain
     *
     * @return HomeMeal
     */
    public function setMealMain($mealMain)
    {
        $this->mealMain = $mealMain;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMealDesert()
    {
        return $this->mealDesert;
    }

    /**
     * @param mixed $mealDesert
     *
     * @return HomeMeal
     */
    public function setMealDesert($mealDesert)
    {
        $this->mealDesert = $mealDesert;

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
}
