<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Voters;

use FOS\UserBundle\Model\User;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Entity\Community\Community;
use Mealmatch\ApiBundle\Entity\Community\CommunityGroup;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CommunityGroupsVoter extends AbstractVoter
{
    /**
     * Community vote.
     *
     * @todo: check edge cases ...
     *
     * @param TokenInterface $token
     * @param mixed          $subject
     * @param array          $attributes
     *
     * @return int
     */
    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        $result = false;
        foreach ($attributes as $attribute) {
            // check if we support the attribute in question ...
            //
            if ($this->supports($attributes, $subject)
               && $this->voteOnAttribute($attribute, $subject, $token)
            ) {
                return self::ACCESS_GRANTED;
            }
        }

        return self::ACCESS_DENIED;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!\in_array($attribute, array(ApiConstants::COMMUNITY_VIEW, 'TODO'), true)) {
            return false;
        }

        // only vote on Community objects inside this voter
        if (!$subject instanceof Community) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        /** @var Community $community */
        $community = $subject;

        switch ($attribute) {
            case ApiConstants::COMMUNITY_VIEW:
                return $this->canView($community, $user);
            case ApiConstants::COMMUNITY_EDIT:
                return $this->canEdit($community, $user);
        }

        throw new \LogicException('Unknown voter attribute: '.$attribute);
    }

    private function canView(Community $community, User $user): bool
    {
        // User has no groups
        if (0 === \count($user->getGroups())) {
            return false;
        }

        // The GROUPS of the User ...
        $groupsOfUser = $user->getGroups()->toArray();
        // The COMMUNITY_GROUPS of this Community
        $cGroups = $community->getGroups()->toArray();
        /** @var CommunityGroup $cGroup */
        foreach ($cGroups as $cGroup) {
            if ($user->hasGroup($cGroup->getGroupName())) {
                // User is in one of the groups of the community...
                // 1 - Check the Roles of the matching cGroup
                $groupRoles = $cGroup->getRoles();
                if (CommunityPermissionHelper::hasCommunityAdminRole($groupRoles)) {
                    // User has the ROLE_COMMUNITY_ADMIN, access granted.
                    return true;
                }
                // 2 - Check Permissions of matching cGroup
                $groupPermission = $cGroup->getPermissions();

                return true;
            }
        }

        if (\in_array(ApiConstants::ROLE_COMMUNITY_ADMIN, $groupsOfUser, true)) {
            // User has the Role: COMMUNITY_ADMIN, access granted.
            return true;
        }
    }

    /**
     * @param Community $community
     * @param User      $user
     *
     * @return bool
     */
    private function canEdit(Community $community, User $user): bool
    {
        // The Roles of the User ...
        $rolesOfUser = $user->getRoles();
        if (\in_array(ApiConstants::ROLE_COMMUNITY_ADMIN, $rolesOfUser, true)) {
            // User has the Role: COMMUNITY_ADMIN, access granted.
            return true;
        }

        return false;
    }
}
