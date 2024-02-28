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
use Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletable;
use Mealmatch\ApiBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass="Mealmatch\ApiBundle\Repository\Meal\MealOfferRepository")
 */
class MealOffer extends AbstractEntity
{
    /*
     * TRAITS
     */
    use
        SoftDeletable;

    /**
     * The unique ID of the MealOffer.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * The name of the MealOffer e.g. "Menu1", "Menu2", "SundaySpecial", "whatever".
     *
     * @var string
     * @ORM\Column(name="name", type="text", length=500)
     */
    protected $name;

    /**
     * The description of this MealOffer.
     *
     * @var string
     * @ORM\Column(name="description", type="text", length=2500)
     */
    protected $description;

    /**
     * The price of this MealOffer.
     *
     * @var float
     * @ORM\Column(name="price", type="float")
     */
    protected $price = 0.0;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var string
     * @ORM\Column(name="currency", type="string")
     */
    protected $currency = 'EUR';

    /**
     * A MealOffer is connected to MealTickets.
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket",
     *     mappedBy="selectedMealOffer",
     *     cascade={"persist","remove"})
     */
    protected $mealTickets;

    /**
     * The available number of offers for the given offer. (amount).
     *
     * @var int
     * @ORM\Column(name="available_amount", type="integer")
     */
    private $availableAmount = 1;

    /**
     * MealOffer constructor.
     */
    public function __construct()
    {
        $this->initHash();
    }

    public function __toString()
    {
        return $this->name.'#'.$this->getId();
    }

    /**
     * The unique ID or null.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return MealOffer
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     *
     * @return MealOffer
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     *
     * @return MealOffer
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return int
     */
    public function getAvailableAmount(): int
    {
        return $this->availableAmount;
    }

    /**
     * @param int $availableAmount
     *
     * @return MealOffer
     */
    public function setAvailableAmount(int $availableAmount): self
    {
        $this->availableAmount = $availableAmount;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return MealOffer
     */
    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

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
     * @return MealOffer
     */
    public function setMealTickets(ArrayCollection $mealTickets): self
    {
        $this->mealTickets = $mealTickets;

        return $this;
    }
}
