<?php
/**
 * Copyright 2016-2017 MealMatch UG
 *
 * Author: Wizard <wizard@mealmatch.de>
 * Created: 02.06.17 09:21
 */

namespace ApiBundle;


use Mealmatch\ApiBundle\MealMatch\UserManager;
use MMUserBundle\Entity\MMUser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserManagerTest extends KernelTestCase
{

    /** @var  UserManager */
    private $userManager;
    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        self::bootKernel();

        $this->userManager = static::$kernel->getContainer()
            ->get('api.user_manager');

    }


    public function testCreateUser()
    {
        /** @var MMUser $user */
        $user = $this->userManager->createUser();
        self::assertNotNull($user, 'Returned user is NULL!');
    }

}