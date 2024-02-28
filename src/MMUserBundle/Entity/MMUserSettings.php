<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User settings are exactly that ... a collection of settings for the user.
 *
 * @ORM\Entity()
 */
class MMUserSettings
{
    /**
     * @todo: Finish PHPDoc!
     *
     * @var bool
     * @ORM\Column(name="email_notification", type="boolean")
     */
    protected $emailNotification = true;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var bool
     * @ORM\Column(name="ui_hints", type="boolean")
     */
    protected $uiHints = true;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    public function __toString()
    {
        return __CLASS__.$this->getId();
    }

    /**
     * @param mixed $id
     *
     * @return MMUserSettings
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isEmailNotification(): bool
    {
        return $this->emailNotification;
    }

    /**
     * @param bool $emailNotification
     *
     * @return MMUserSettings
     */
    public function setEmailNotification(bool $emailNotification): self
    {
        $this->emailNotification = $emailNotification;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUiHints(): bool
    {
        return $this->uiHints;
    }

    /**
     * @param bool $uiHints
     *
     * @return MMUserSettings
     */
    public function setUiHints(bool $uiHints): self
    {
        $this->uiHints = $uiHints;

        return $this;
    }
}
