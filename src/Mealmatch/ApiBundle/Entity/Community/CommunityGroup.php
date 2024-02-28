<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Entity\Community;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\Group as BaseGroup;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\MealMatch\Traits\ToStringable;

/**
 * @ORM\Entity()
 * @ORM\Table(name="community_group")
 */
class CommunityGroup extends BaseGroup
{
    use ToStringable;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Community Group NAME.
     *
     * @ORM\Column(type="string", length=124)
     */
    protected $groupName;

    /**
     * Community Group ROLES.
     *
     * @var array
     * @ORM\Column(type="array")
     */
    protected $groupRoles;

    /**
     * Community Group Properties.
     *
     * @var string
     * @ORM\Column(type="json_array")
     */
    protected $properties = array();

    /**
     * Community Group Permissions.
     *
     * @var string
     * @ORM\Column(type="json_array")
     */
    protected $permissions = array();

    public function __construct()
    {
        // Every community group has these roles
        $this->groupRoles = array('COMMUNITY_GROUP_ADMIN', 'COMMUNITY_GROUP_MEMBER', 'COMMUNITY_GROUP_OWNER');
        $this->groupName = 'DEFAULT_C_GROUP_NAME';
        parent::__construct(
            'DEFAULT_C_GROUP_NAME',
            array('COMMUNITY_GROUP_ADMIN', 'COMMUNITY_GROUP_MEMBER', 'COMMUNITY_GROUP_OWNER')
        );
    }

    /**
     * The name of the Community group.
     *
     * The group suffix '_C_GROUP' is always appended;
     *
     * @return mixed
     */
    public function getGroupName()
    {
        return $this->groupName.ApiConstants::MEALMATCH_COMMUNITY_GROUP_SUFFIX;
    }

    /**
     * @param mixed $groupName
     *
     * @return CommunityGroup
     */
    public function setGroupName($groupName): self
    {
        $this->groupName = $groupName;
        $this->setName($groupName);

        return $this;
    }

    /**
     * @return array
     */
    public function getGroupRoles(): array
    {
        return $this->groupRoles;
    }

    /**
     * @param array $groupRoles
     *
     * @return CommunityGroup
     */
    public function setGroupRoles(array $groupRoles): self
    {
        $this->setRoles($groupRoles);
        $this->groupRoles = $groupRoles;

        return $this;
    }

    /**
     * @return string
     */
    public function getProperties(): string
    {
        return json_encode($this->properties);
    }

    /**
     * @param string $properties
     *
     * @return CommunityGroup
     */
    public function setProperties(string $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @return string
     */
    public function getPermissions(): string
    {
        return json_encode($this->permissions);
    }

    /**
     * @param string $permissions
     *
     * @return CommunityGroup
     */
    public function setPermissions(string $permissions): self
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * @param $groupRole
     *
     * @return CommunityGroup
     */
    public function addGroupRole(string $groupRole): self
    {
        if (!$this->hasGroupRole($groupRole)) {
            $this->groupRoles[] = strtoupper($groupRole);
        }

        return $this;
    }

    /**
     * @param $groupRole
     *
     * @return bool
     */
    public function hasGroupRole($groupRole): bool
    {
        return \in_array(strtoupper($groupRole), $this->groupRoles, true);
    }
}
