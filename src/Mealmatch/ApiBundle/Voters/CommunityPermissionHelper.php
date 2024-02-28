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
use Mealmatch\ApiBundle\Exceptions\MealmatchException;

class CommunityPermissionHelper
{
    /**
     * Returns true if the array contains a value matching ROLE_COMMUNITY_ADMIN;.
     *
     * @param array $needleArray
     *
     * @return bool
     */
    public static function hasCommunityAdminRole(array $needleArray): bool
    {
        if (\in_array(ApiConstants::ROLE_COMMUNITY_ADMIN, $needleArray, true)) {
            // User has the Role: COMMUNITY_ADMIN, access granted.
            return true;
        }
    }

    /**
     * Returns true if the array contains a value matching ROLE_COMMUNITY_ADMIN;.
     *
     * @param array $needleArray
     *
     * @return bool
     */
    public static function hasCommunityMemberRole(array $needleArray): bool
    {
        if (\in_array(ApiConstants::ROLE_COMMUNITY_MEMBER, $needleArray, true)) {
            // User has the Role: COMMUNITY_ADMIN, access granted.
            return true;
        }
    }

    public static function isCommunityMember(User $user, Community $community): bool
    {
        // todo: implement this method!
        throw new MealmatchException('This has not been implemented yet!');

        return false;
    }
}
