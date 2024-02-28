<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\MealMatch\Traits;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Util\TokenGenerator;
use InvalidArgumentException;

/**
 * This trait add a "HASH" into your table using the FOS TokenGenerator.
 *
 * In your entity CONSTRUCTOR call $this->initHash();
 *
 * OR set the value using your own methods.
 *
 * I advise not to do both! ;)
 */
trait Hashable
{
    /**
     * The HASH is in here.
     *
     * @var string
     *
     * @ORM\Column(name="hash", type="string", length=190, unique=true)
     */
    protected $hash;

    /**
     * Checks if the hash value has been generated.
     *
     * The initialized default value is "null". After setHash(), initHash() has been called the value
     *
     *
     * @return bool true if a hash value exists, otherwise false
     */
    public function isHashed(): bool
    {
        // is is hashed? ... yes->it IS NOT NULL, no->it IS NULL
        return null !== $this->getHash();
    }

    /**
     * Returns the value of the HASH.
     *
     * @return string|null the HASH
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Sets the HASH value.
     *
     * @param string $hash the value to set
     *
     * @return $this fluent
     */
    public function setHash(string $hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Rebuilds the hash value.
     */
    public function rebuildHash()
    {
        $tokenG = new TokenGenerator();
        try {
            $this->setHash($tokenG->generateToken());
        } catch (InvalidArgumentException $invalidArgumentException) {
            // this would be soo bad! ...
        }
    }

    /**
     * Will initialize the current HASH value only if not null!
     *
     * This is where the TokenGenerator is used. Use this method inside your entity constructor.
     */
    protected function initHash()
    {
        if (null !== $this->hash) {
            return;
        }
        $tokenG = new TokenGenerator();
        try {
            $this->setHash($tokenG->generateToken());
        } catch (InvalidArgumentException $invalidArgumentException) {
            // this would be soo bad! ...
        }
    }
}
