<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class LogEntry does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 *
 * @ORM\Entity()
 */
class LogEntry
{
    /*
     * Traits
     */
    use ORMBehaviors\Timestampable\Timestampable;
    use
        ORMBehaviors\Blameable\Blameable;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int the id
     */
    private $id;

    /**
     * @ORM\Column(name="message", type="text")
     *
     * @var string the log message
     */
    private $message;

    /**
     * @ORM\Column(name="level", type="string", length=50)
     *
     * @var string the log level
     */
    private $level;

    /**
     * @ORM\Column(name="request_infos", type="json_array", nullable=true)
     *
     * @var string
     */
    private $requestInfos;

    /**
     * @ORM\Column(name="channel", type="string", length=64, nullable=false)
     *
     * @var string - the channel this LogEntry is associated to
     */
    private $channel = 'default';

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return LogEntry
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return LogEntry
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param string $level
     *
     * @return LogEntry
     */
    public function setLevel(string $level): self
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRequestInfos()
    {
        return $this->requestInfos;
    }

    /**
     * @param string $requestInfos
     *
     * @return LogEntry
     */
    public function setRequestInfos(string $requestInfos): self
    {
        $this->requestInfos = $requestInfos;

        return $this;
    }

    /**
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * @param string $channel
     *
     * @return LogEntry
     */
    public function setChannel(string $channel): self
    {
        $this->channel = $channel;

        return $this;
    }
}
