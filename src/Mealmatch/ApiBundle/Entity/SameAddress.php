<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mealmatch\ApiBundle\Entity\Meal\MealAddress;

/**
 * @ORM\Entity(repositoryClass="Mealmatch\ApiBundle\Repository\SameAddressRepository")
 */
class SameAddress extends AbstractEntity
{
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
     * @todo: Finish PHPDoc!
     *
     * @var string
     * @ORM\Column(name="combined_location_string", length=255, nullable=false)
     */
    private $combinedLocationString;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var Collection
     * @ORM\ManyToMany(targetEntity="Mealmatch\ApiBundle\Entity\Meal\MealAddress")
     * @ORM\JoinTable(name="same_address_to_meal_address",
     *      joinColumns={@ORM\JoinColumn(name="meal_address_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="same_address_id", referencedColumnName="id", unique=true)}
     *      )
     */
    private $mealAddresses;

    public function __construct()
    {
        parent::__construct();
        if (!$this->isHashed()) {
            $this->initHash();
        }
        $this->mealAddresses = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function getMealAddresses()
    {
        return $this->mealAddresses;
    }

    /**
     * @param Collection $mealAddresses
     *
     * @return SameAddress
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

    /**
     * @return string
     */
    public function getCombinedLocationString(): string
    {
        return $this->combinedLocationString;
    }

    /**
     * @param string $combinedLocationString
     *
     * @return SameAddress
     */
    public function setCombinedLocationString(string $combinedLocationString): self
    {
        $this->combinedLocationString = $combinedLocationString;

        return $this;
    }
}
