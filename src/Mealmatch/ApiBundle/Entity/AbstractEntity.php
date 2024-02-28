<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Blameable\Blameable;
use Knp\DoctrineBehaviors\Model\Timestampable\Timestampable;
use Mealmatch\ApiBundle\MealMatch\Traits\Hashable;
use Mealmatch\ApiBundle\MealMatch\Traits\ToStringable;

/**
 * Base entity class with ID.
 *
 * Extend from this class to safe lines of code.
 */
abstract class AbstractEntity implements EntityData
{
    /*
     * TRAITS
     */

    use Timestampable;
    use ToStringable;
    use Blameable;
    use Hashable;

    /**
     * The unique ID of the entity.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function __construct()
    {
        $this->initHash();
    }

    /**
     * Returns the unique ID of the entity or NULL if the entity is not managed.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    public function getFQDN(): string
    {
        return __NAMESPACE__.__CLASS__;
    }

    public function getUID(): string
    {
        return __NAMESPACE__.__CLASS__.'/ID/'.$this->getId();
    }

    public function getUUID(): string
    {
        return __NAMESPACE__.__CLASS__.'/HASH/'.$this->getHash();
    }
}
