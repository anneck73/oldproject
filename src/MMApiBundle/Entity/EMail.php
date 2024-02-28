<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use WhiteOctober\SwiftMailerDBBundle\EmailInterface;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class EMail does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 * @ORM\Table(name="email")
 * @ORM\Entity(repositoryClass="MMApiBundle\Repository\EMailRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class EMail implements EmailInterface
{
    /**
     * @todo: Finish PHPDoc!
     *
     * @var string the EmailMessage Blob
     * @ORM\Column(name="message", type="text")
     */
    private $message;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var string the Status of the Mail delivery process
     * @ORM\Column(name="status", type="string", length=25)
     */
    private $status;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var string the environment
     * @ORM\Column(name="environment", type="string", length=255)
     */
    private $environment;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return EMail
     */
    public function setId(int $id): self
    {
        $this->id = $id;

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
     * @return EMail
     */
    public function setMessage($message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return EMail
     */
    public function setStatus($status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * @param string $environment
     *
     * @return EMail
     */
    public function setEnvironment($environment): self
    {
        $this->environment = $environment;

        return $this;
    }
}
