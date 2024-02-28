<?php
/**
 * Copyright (c) 2017. Mealmatch GmbH
 * Author: Wizard <wizard@mealmatch.de>
 */

namespace PayPalBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Goutte\Client as WebClient;
use Mealmatch\PayPalBundle\PayPalStatusValues;
use MMApiBundle\Entity\Meal;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class PaymentControllerTest does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 * @noinspection SuspiciousAssignmentsInspection
 */
class PayPalControllerTest extends WebTestCase
{
    /** @var Client */
    private $client;

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $myArgument With a *description* of this argument, these may also
     *    span multiple lines.
     */
    public function setUp()
    {
        // $this->client = static::createClient();
        $this->client = static::createClient(
            [],
            []
        );
        /** @var Crawler $crawler */
        $crawler = $this->logIn('MMTestHost');
        /** @noinspection SuspiciousAssignmentsInspection */

        $crawler = $this->client->request('GET', '/api/meal/');
        // $this->deleteAllMyMeals($crawler);

    }
    public function tearDown()
    {

        /** @var Crawler $crawler */
        $crawler = $this->logIn('MMTestHost');
        /** @noinspection SuspiciousAssignmentsInspection */
/*
        $crawler = $this->client->request('GET', '/api/meal/');
        $this->deleteAllMyMeals($crawler);
*/
    }

    public function testPaymentScenarioZero()
    {

        static::markTestSkipped('Test needs re-write!');
        /** @var Crawler $crawler */
        $crawler = $this->logIn('MMTestHost');

        /** @noinspection SuspiciousAssignmentsInspection */
        $crawler = $this->client->request('GET', '/api/meal/new');

        $form = $crawler->selectButton('_new_meal')->form();

        // set some values
        $form['mmapibundle_meal[title]'] = 'TestMeal-PaymentScenarioZero';
        $form['mmapibundle_meal[main]'] = 'TestNudelnMain';
        $form['mmapibundle_meal[sharedCost]'] = '5.00';
        $form['mmapibundle_meal[locationAddress]'] = 'Petersburgerstraße 69, Berlin';
        $form['mmapibundle_meal[startDateTime]'] = '4.2.4242 16:20';

        // submit the form
        $crawler = $this->client->submit($form);

        $this->assertTrue(
            $this->client->getResponse()->isRedirect(),
            $this->client->getResponse()->getContent()
        );

        $crawler = $this->client->followRedirect();
        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            $this->client->getResponse()->getContent()
        );

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("TestMeal-PaymentScenarioZero")')->count(),
            $this->client->getResponse()->getContent()
        );

        // logout user ...
        $this->client->request('GET', '/logout');

        /** @var Crawler $crawler */
        $crawler = $this->logIn('MMTestGuest');

        // Search for Meal ...
        /** @noinspection SuspiciousAssignmentsInspection */
        $crawler = $this->client->request('GET', '/s/?searchLocation=Überall&datetimeMod=4.2.4242&searchCategory=');

        $link = $crawler
            ->filter('a:contains("Teilnehmen")') // find all links with the text "Teilnehmen"
            ->eq(0) // select the first link in the list
            ->link()
        ;

        // and click it
        $crawler = $this->client->click($link);

        // Anfrage zur Teilnahme
        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            $this->client->getResponse()->getContent()
        );

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Anfrage zur Teilnahme")')->count(),
            $this->client->getResponse()->getContent()
        );


        $form = $crawler->selectButton('Anfrage absenden')->form();
        $form['mmapibundle_joinrequest[messageToHost]'] = 'Freundlichetestanfrage! :)';

        $crawler = $this->client->submit($form);
        /** @noinspection SuspiciousAssignmentsInspection */
        $crawler = $this->client->followRedirect();
        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            $this->client->getResponse()->getContent()
        );


        // Meine Matches?...
        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            $this->client->getResponse()->getContent()
        );

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Meine Matches")')->count(),
            $this->client->getResponse()->getContent()
        );

        // logout user ...
        $this->client->request('GET', '/logout');

        /** @var Crawler $crawler */
        $crawler = $this->logIn('MMTestHost');

        /** @noinspection SuspiciousAssignmentsInspection */
        $crawler = $this->client->request('GET', '/api/joinrequest/');

        $link = $crawler
            ->filter('a:contains("Akzeptieren")') // find all links with the text "Teilnehmen"
            ->eq(0) // select the first link in the list
            ->link()
        ;

        // and click it
        $crawler = $this->client->click($link);

        // Anfrage zur Teilnahme
        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            $this->client->getResponse()->getContent()
        );

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Anfrage annehmen")')->count(),
            $this->client->getResponse()->getContent()
        );

        $form = $crawler->selectButton('Anfrage annehmen')->form();
        $form['mmapibundle_joinrequest[messageToGuest]'] = 'Freundlichetestantwort! :)';

        // submit the form
        $crawler = $this->client->submit($form);

        /** @noinspection SuspiciousAssignmentsInspection */
        $crawler = $this->client->followRedirect();
        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            $this->client->getResponse()->getContent()
        );

        // Meine Matches...
        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            $this->client->getResponse()->getContent()
        );

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Wartet auf Bezahlungsvorgang")')->count(),
            $this->client->getResponse()->getContent()
        );


        // logout user ...
        $this->client->request('GET', '/logout');

        /** @var Crawler $crawler */
        $crawler = $this->logIn('MMTestGuest');

        /** @noinspection SuspiciousAssignmentsInspection */
        $crawler = $this->client->request('GET', '/api/joinrequest/');
        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            $this->client->getResponse()->getContent()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("TestMeal-PaymentScenarioZero")')->count(),
            $this->client->getResponse()->getContent()
        );

        $link = $crawler
            ->filter('a:contains("zusagen")')
            ->eq(0) // select the first link in the list
            ->link()
        ;

        // and click it
        $this->client->click($link);

        // get the targetURL to prepare payment call ...
        $resp = $this->client->getResponse();
        if($resp instanceof RedirectResponse) {
            $targetURL = $resp->getTargetUrl();
        } else  {
            $this->fail('Could not redirect to paypal');
        }

        // Now to pay pal ...
        $payPalClient = new WebClient();
        /** @noinspection PhpUndefinedVariableInspection */
        $crawler = $payPalClient->request('GET', $targetURL);

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("test facilitator\'s ServiceDataManagerTest Store")')->count(),
            $this->client->getResponse()->getContent()
        );

        $entityMngr = $this->client->getKernel()->getContainer()->get('doctrine.orm.default_entity_manager');

        /** @var array $meals */
        $meals = $entityMngr->getRepository('MMApiBundle:Meal')->findByTitle('TestMeal-PaymentScenarioZero');
        /** @var Meal $meal */
        $meal = $meals[0];
        $mealID = $meal->getId();
        /** @var array $mealTickets */
        $mealTickets = $entityMngr->getRepository('MMApiBundle:MealTicket')->findBy(['meal' => $meal]);
        /** @var  $mealTicket */
        $mealTicket = $mealTickets[0];
        $mealTicketCol = new ArrayCollection($mealTickets);
        $mealTicketCol->getIterator()->count();

        // enforce it ...
        $paymentTokenCompleted = $this->client->getContainer()->get('mealmatch_paypal.payment_token')
            ->createOnNotify($mealTicket,
            ['pay_key' => '', 'status' => PayPalStatusValues::IPN_STATUS_COMPLETED], true);

        $this->client->getContainer()->get('mealmatch_paypal.manager')
            ->updateMealTicketOnNotify($mealTicket, $paymentTokenCompleted);

        // enforce it ...
        $paymentTokenProcessed = $this->client->getContainer()->get('mealmatch_paypal.payment_token')
            ->createOnNotify($mealTicket,
                ['pay_key' => '', 'status' => PayPalStatusValues::IPN_STATUS_PROCESSED], true);

        $this->client->getContainer()->get('mealmatch_paypal.manager')
            ->updateMealTicketOnNotify($mealTicket, $paymentTokenProcessed);

        // enforce it ...
        $paymentTokenCreated = $this->client->getContainer()->get('mealmatch_paypal.payment_token')
            ->createOnNotify($mealTicket,
                ['pay_key' => '', 'status' => PayPalStatusValues::IPN_STATUS_CREATED], true);

        $this->client->getContainer()->get('mealmatch_paypal.manager')
            ->updateMealTicketOnNotify($mealTicket, $paymentTokenCreated);

        // enforce it ...
        $paymentTokenFailed = $this->client->getContainer()->get('mealmatch_paypal.payment_token')
            ->createOnNotify($mealTicket,
                ['pay_key' => '', 'status' => PayPalStatusValues::IPN_STATUS_FAILED], true);

        $this->client->getContainer()->get('mealmatch_paypal.manager')
            ->updateMealTicketOnNotify($mealTicket, $paymentTokenFailed);

        $entityMngr->flush();


    }

    /**
     * Login method ...
     *
     * @return Crawler $crawler instance after login redirect
     */
    private function logIn($pUsername = 'MMTestGuest')
    {
        $session = $this->client->getContainer()->get('session');

        // the firewall context (defaults to the firewall name)
        $firewall = 'main';

        /** @var EntityManager $em */
        $em = $this->client->getContainer()->get('doctrine.orm.default_entity_manager');
        $user = $em->getRepository('MMUserBundle:MMUser')->findOneByUsername($pUsername);

        $token = new UsernamePasswordToken($user, $user->getPassword(), $firewall, array('ROLE_USER'));

        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);


        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Anmelden')->form();
        $form['_username'] = $pUsername;
        $form['_password'] = 'MMTest';

        $crawler = $this->client->submit($form);

        return $this->client->followRedirect();

    }

    private function deleteAllMyMeals($crawler)
    {
        // Delete All Meals
        $allLinks = $crawler->filter('a')->extract(['name', 'href']);
        foreach ($allLinks as $link) {
            if ($link[0] === 'meal_edit_link') {
                $crawler = $this->client->request('GET', $link[1]);
                $form = $crawler->selectButton('meal_delete')->form();
                $crawler = $this->client->submit($form);
                /** @noinspection SuspiciousAssignmentsInspection */
                $crawler = $this->client->followRedirect();
            }
        }
    }
}
