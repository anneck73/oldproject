<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\GameLogicBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Blameable\Blameable;
use Knp\DoctrineBehaviors\Model\Geocodable\Geocodable;
use Knp\DoctrineBehaviors\Model\Sortable\Sortable;
use Knp\DoctrineBehaviors\Model\Timestampable\Timestampable;
use Mealmatch\GameLogicBundle\Core\ScoreInterface;
use Symfony\Component\Security\Core\User\UserInterface as User;

/**
 * The Score entity class is used to store scores.
 *
 * @ORM\Entity(repositoryClass="Mealmatch\GameLogicBundle\Repository\ScoreRepository")
 * @ORM\Table(name="mm_game_score")
 */
class Score implements ScoreInterface
{
    /*
     * Traits
     */
    use Blameable;
    use
        Geocodable;
    use
        Sortable;
    use
        Timestampable;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id = -1;

    /**
     * @ORM\Column(name="name", type="string", length=64)
     */
    private $name = 'undefined';

    /**
     * @ORM\Column(name="type", type="string", length=64)
     */
    private $type = 'undefined';

    /**
     * @ORM\Column(name="value", type="integer")
     */
    private $value = 0;

    public function __toString()
    {
        if (null === $this->getCreatedBy()) {
            return $this->getName().$this->getType().$this->getValue();
        }

        return $this->getName().$this->getType().$this->getValue().$this->getUser();
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return Score
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     *
     * @return Score
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     *
     * @return Score
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return User
     */
    public function getUser(): User
    {
        return $this->getCreatedBy();
    }
}
