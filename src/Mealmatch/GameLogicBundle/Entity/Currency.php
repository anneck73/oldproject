<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\GameLogicBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mealmatch\ApiBundle\MealMatch\Traits\Hashable;

/**
 * @todo: Finish PHPDoc!
 *
 * The game currency ...
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 *
 *
 *
 * @ORM\Entity
 * @ORM\Table(name="mm_game_currency")
 */
class Currency
{
    /*
     * Traits
     */
    use Hashable;
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * The currency code 3 characters long.
     *
     * @ORM\Column(type="string", name="code", length=3)
     */
    private $code;
    /**
     * The name of the currency.
     *
     * @ORM\Column(type="string", name="name", length=32)
     */
    private $name;
    /**
     * @todo: Finish PHPDoc!
     * @ORM\Column(type="float", name="base_value")
     */
    private $baseValue;

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     *
     * @return Currency
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
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
     * @return Currency
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBaseValue()
    {
        return $this->baseValue;
    }

    /**
     * @param mixed $baseValue
     *
     * @return Currency
     */
    public function setBaseValue($baseValue)
    {
        $this->baseValue = $baseValue;

        return $this;
    }
}
