<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Entity\Community;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\GroupableInterface;
use FOS\UserBundle\Model\GroupInterface;
use Mealmatch\ApiBundle\Entity\AbstractEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Community.
 *
 * @ORM\Entity()
 * @ORM\Table(name="community")
 */
class Community extends AbstractEntity implements GroupableInterface
{
    /**
     * The name of the community.
     *
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="name", type="string", length=128)
     */
    protected $name;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $private = false;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="Mealmatch\ApiBundle\Entity\Community\CommunityGroup", cascade={"persist"})
     * @ORM\JoinTable(name="community_to_group",
     *      joinColumns={@ORM\JoinColumn(name="community_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     *      )
     */
    protected $groups;

    /**
     * Community Properties.
     *
     * @var string
     * @ORM\Column(type="json_array")
     */
    protected $properties;

    /**
     * Community Permissions.
     *
     * @var string
     * @ORM\Column(type="json_array")
     */
    protected $permissions;

    public function __construct()
    {
        parent::__construct();
        $this->groups = new ArrayCollection();
        $this->permissions = json_encode(array());
        $this->properties = json_encode(array());
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Community
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    /**
     * @param Collection $groups
     *
     * @return Community
     */
    public function setGroups(Collection $groups): self
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * @return string
     */
    public function getProperties(): ?string
    {
        return $this->properties;
    }

    /**
     * @param string $properties
     *
     * @return Community
     */
    public function setProperties(string $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @return string
     */
    public function getPermissions(): ?string
    {
        return $this->permissions;
    }

    /**
     * @param string $permissions
     *
     * @return Community
     */
    public function setPermissions(string $permissions): self
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->private;
    }

    /**
     * @param bool $private
     *
     * @return Community
     */
    public function setPrivate(bool $private): self
    {
        $this->private = $private;

        return $this;
    }

    /**
     * @return array
     */
    public function getGroupNames()
    {
        // TODO: Implement getGroupNames() method.
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasGroup($name)
    {
        // TODO: Implement hasGroup() method.
    }

    /**
     * @param GroupInterface $group
     *
     * @return $this
     */
    public function addGroup(GroupInterface $group)
    {
        // TODO: Implement addGroup() method.
    }

    /**
     * @param GroupInterface $group
     *
     * @return $this
     */
    public function removeGroup(GroupInterface $group)
    {
        // TODO: Implement removeGroup() method.
    }
}
