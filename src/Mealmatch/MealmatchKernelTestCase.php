<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch;

use Doctrine\ORM\EntityManager;
use MMUserBundle\Entity\MMUser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class MealmatchKernelTestCase extends KernelTestCase
{
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        KernelTestCase::bootKernel();

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        /* For unit testing where it is not necessary to persist the session, you should simply swap out the default
         * storage engine with MockArraySessionStorage
         * https://symfony.com/doc/3.4/components/http_foundation/session_testing.html#unit-testing
         */
        $this->session = new Session(new MockArraySessionStorage());
        $this->session->start();
    }

    /**
     * Helper method to do a "login" with the given user.
     *
     * This method is used to put the user into the session in order for createdBy, updatedBy, deletedBy to work!
     *
     * @param string $userName
     */
    protected function login(string $userName)
    {
        // the firewall context (defaults to the firewall name)
        $firewall = 'main';

        /** @var MMUser $user */
        $user = $this->em->getRepository('MMUserBundle:MMUser')->findOneByUsername($userName);

        $token = new UsernamePasswordToken($user, $user->getPassword(), $firewall, $user->getRoles());
        static::$kernel->getContainer()->get('security.token_storage')->setToken($token);
        static::$kernel->getContainer()->get('fos_user.security.login_manager')->logInUser($firewall, $user);
        $this->session->set('_security_'.$firewall, serialize($token));
        $this->session->save();
    }
}
