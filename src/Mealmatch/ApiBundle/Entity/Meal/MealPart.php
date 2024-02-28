<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Entity\Meal;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Mealmatch\ApiBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass="Mealmatch\ApiBundle\Repository\Meal\MealPartRepository")
 */
class MealPart extends AbstractEntity implements MealData
{
    /**
     * The unique ID of the MealPart.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var int
     * @Gedmo\SortablePosition()
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    protected $position;

    /**
     * The name of the MealPart e.g. "main", "desert", "starter", "whatever".
     *
     * @var string
     * @ORM\Column(name="name", type="string", length=25)
     */
    private $name;

    /**
     * The description of this MealPart.
     *
     * @var string
     * @ORM\Column(name="description", type="text", length=2500)
     */
    private $description;

    public function __construct()
    {
        $this->initHash();
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
     * @return MealPart
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
     * @return MealPart
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param mixed $position
     *
     * @return MealPart
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }
}
