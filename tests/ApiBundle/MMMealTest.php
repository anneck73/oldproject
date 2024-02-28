<?php
/**
 * Copyright (c) 2017. Mealmatch GmbH
 * Author: Wizard <wizard@mealmatch.de>
 */

namespace MMApiBundle;

use DateTime;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class MMMealTest extends KernelTestCase
{

    /**
     * @todo: Finish PHPDoc!
     * @var MMMeal mealService - The mm.meal service.
     */
    private $mealService;

    public function testCreateMealDefault()
    {
        $this->setUp();
        $user = $this->logIn();
        $now = new DateTime('now');
        $startDateTime = $now->modify('+4 hours');

        $mealData = $this->mealService->createBySpecification(
            $user,
            $startDateTime,
            'Langhansstraße 116, 13086 Berlin'
        );

        self::assertEquals(MMMeal::DEFAULT_SPEC, $mealData->get('specification'));
    }

    public function setUp()
    {
        static::bootKernel();
        $this->mealService = static::$kernel->getContainer()->get('mm.meal');
    }

    private function logIn()
    {
        $session = static::$kernel->getContainer()->get('session');

        // the firewall context (defaults to the firewall name)
        $firewall = 'main';

        /** @var EntityManager $em */
        $em = static::$kernel->getContainer()->get('doctrine.orm.default_entity_manager');
        $user = $em->getRepository('MMUserBundle:MMUser')->findOneByUsername('MMTestGuest');

        $token = new UsernamePasswordToken($user, $user->getPassword(), $firewall, array('ROLE_ADMIN'));
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        return $user;

        // $cookie = new Cookie($session->getName(), $session->getId());
        // static::$kernel->getCookieJar()->set($cookie);
    }


}
