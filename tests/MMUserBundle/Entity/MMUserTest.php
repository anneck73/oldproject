<?php
/**
 * Copyright 2016-2017 MealMatch UG
 *
 * Author: Wizard <wizard@mealmatch.de>
 * Created: 08.06.17 18:30
 */

namespace MMUserBundle\Entity;

use Mealmatch\MealmatchKernelTestCase;

class MMUserTest extends MealmatchKernelTestCase
{

    public function testCreateHomeUser()
    {
        $userManager = static::$kernel->getContainer()
            ->get('api.user_manager');
        /** @var MMUser $MMUser */
        $MMUser = $userManager->createHomeUser();

        self::assertNotNull($MMUser);

        self::assertTrue($MMUser->hasRole('ROLE_HOME_USER'));
    }

    public function testCreateRestaurantUser()
    {
        $userManager = static::$kernel->getContainer()
            ->get('api.user_manager');
        /** @var MMUser $MMUser */
        $MMUser = $userManager->createRestaurantUser();

        self::assertNotNull($MMUser);

        self::assertTrue($MMUser->hasRole('ROLE_RESTAURANT_USER'));
    }

    public function testGetMealmatchUser()
    {
        static::markTestSkipped('Test needs re-write!');
        $userManager = static::$kernel->getContainer()
            ->get('api.user_manager');

        // $MMUser = $userManager->createUser();

        $MMUser = $userManager->getMealmatchUser('MMTestGuest');

        self::assertNotNull($MMUser);

        $MMUserByEmail = $userManager->getMealmatchUser('mmtest.payer@mealmatch.de');

        self::assertNotNull($MMUser);

    }

    public function testGetMealmatchUserException()
    {
        $userManager = static::$kernel->getContainer()
            ->get('api.user_manager');

        $this->expectException('Mealmatch\ApiBundle\Exceptions\UserNotFoundException');
        $MMUser = $userManager->getMealmatchUser('DoesNotExist');

    }

}
