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
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CommunityRolesVoter extends AbstractVoter
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
    public function vote(TokenInterface $token, $subject, array $attributes)
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
        // Have an on/off switch for the community as a whole.
        // todo: return !$community->isLive();

        // if they can edit, they can view
        if ($this->canEdit($community, $user)) {
            return true;
        }

        if (CommunityPermissionHelper::hasCommunityMemberRole()) {

        // Private/Invite-only communities, view permission is not enough.
            return !$community->isPrivate();
        }
        // todo: the above does not check for the view
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

        return CommunityPermissionHelper::hasCommunityAdminRole($rolesOfUser);
    }
}
