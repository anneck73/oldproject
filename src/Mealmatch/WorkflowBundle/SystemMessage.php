<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\WorkflowBundle;

use MMUserBundle\Entity\MMUser;

/**
 * Just a bean ... used by methods to send SystemMessages via FOS:Message.
 */
class SystemMessage
{
    /** @var MMUser $recipient */
    private $recipient;
    /** @var MMUser $sender */
    private $sender;
    /** @var string $subject */
    private $subject;
    /** @var string $message */
    private $message;

    /**
     * @return MMUser
     */
    public function getRecipient(): MMUser
    {
        return $this->recipient;
    }

    /**
     * @param MMUser $recipient
     *
     * @return SystemMessage
     */
    public function setRecipient(MMUser $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * @return MMUser
     */
    public function getSender(): MMUser
    {
        return $this->sender;
    }

    /**
     * @param MMUser $sender
     *
     * @return SystemMessage
     */
    public function setSender(MMUser $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     *
     * @return SystemMessage
     */
    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return SystemMessage
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
