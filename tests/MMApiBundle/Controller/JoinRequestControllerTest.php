<?php

/*
 * Copyright (c) 2016-2017. Mealmatch GmbH
 * (c) Markus Verkoyen <markus.verkoyen@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMApiBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Mealmatch\MealmatchKernelTestCase;
use MMUserBundle\Entity\MMUser;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class JoinRequestControllerTest
 * @package MMApiBundle\Tests\Controller
 */
class JoinRequestControllerTest extends WebTestCase
{
    /* @var Client $client*/
    protected $client;

    /**
     * @var Session $session
     */
    protected $session;
    /**
     * @var EntityManager $em
     */
    protected $em;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        KernelTestCase::bootKernel();

        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'MMTestGuest',
            'PHP_AUTH_PW'   => 'MMTest',
        ));

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

        $session = $this->client->getContainer()->get('session');


    }


    /**
     * Stand 08.02.18 soll diese Methode sicherstellen, das man nach einem Klick auf Zahlungspflichtig zusagen im
     * richtigen Mealticket landet.
     */
    public function testApiMealticketIdCreateAction()
    {
//        $this->login('MMTestGuest');

        /* @var Crawler $crawler */
        $crawler = $this->client->request('GET', '/api/joinrequest');

        $this->assertTrue(
            $this->client->getResponse()->isRedirect(),
            $this->client->getResponse()->getContent()
        );
        $crawler = $this->client->followRedirect();
        $this->assertEquals(
            1,
            $crawler->filter('h2:contains("Meine Matches")')->count()
        );
        $this->assertGreaterThanOrEqual(
            1,
            $crawler->filter('html:contains("Zahlungspflichtig zusagen")')->count()
        );

        $link = $crawler->selectLink('Zahlungspflichtig zusagen')->first()->link();
        $crawler = $this->client->click($link);

        $this->assertTrue(
            $this->client->getResponse()->isRedirect(),
            $this->client->getResponse()->getContent()
        );
        $crawler = $this->client->followRedirect();
        $response = $this->client->getResponse();
        $this->assertEquals(
            1,
            $crawler->filter('body:contains("Bezahlvorgang")')->count(),
            $response

        );
    }

}
