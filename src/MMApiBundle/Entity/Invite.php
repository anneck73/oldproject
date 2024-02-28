<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class Invite does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 * @ORM\Table(name="invites")
 * @ORM\Entity(repositoryClass="MMApiBundle\Repository\InviteRepository")
 */
class Invite
{
    /*
     * Traits
     * */
    use ORMBehaviors\Blameable\Blameable;
    use
        ORMBehaviors\Timestampable\Timestampable;
    /**
     * @var string
     * @ORM\Column(name="email_used", type="string", length=190, unique=true)
     */
    private $emailUsed;

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
     * @return string
     */
    public function getEmailUsed(): string
    {
        return $this->emailUsed;
    }

    /**
     * @param string $emailUsed
     *
     * @return Invite
     */
    public function setEmailUsed(string $emailUsed): self
    {
        $this->emailUsed = $emailUsed;

        return $this;
    }
}
