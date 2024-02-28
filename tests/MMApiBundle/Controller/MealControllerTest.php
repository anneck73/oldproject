<?php
/**
 * Copyright (c) 2017. Mealmatch GmbH
 * Author: Wizard <wizard@mealmatch.de>
 */

namespace MMApiBundle\Controller;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class MealControllerTest extends WebTestCase
{
    /** @var Client */
    private $client;

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     */
    public function setUp()
    {
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'MMTestHost',
            'PHP_AUTH_PW' => 'MMTest',
        ));

    }

    public function testApiMealIndex()
    {
        $crawler = $this->client->request('GET', '/api/meal/');

        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            'Meal Index Failed: ' . $this->client->getResponse()->getStatusCode()
        );
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Meine Meals")')->count());
    }

    public function testApiHomeMealNewScenario1()
    {

        $crawler = $this->client->request('GET', '/api/homemeal/new');

        $this->assertTrue(
            $this->client->getResponse()->isRedirect(),
            $this->client->getResponse()->getContent()
        );

        $crawler = $this->client->followRedirect();
        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            $this->client->getResponse()->getContent()
        );

        $this->assertGreaterThan(0,
            $crawler->filter('html:contains("Home-Meals verwalten")')->count()
        );

        // We need the id of the meal to publish it later
        $mealId = preg_replace('![^0-9]!', '', $crawler->getUri());
        // Since we only making offers public we need to increase id + 1
        ++$mealId;

        $link = $crawler->selectLink('Meal (Neu)')->link();
        $crawler = $this->client->click($link);

        // TESTING TAB1
        $form = $crawler->filter('form[name=mealmatch_apibundle_meal_homemeal_tab_one]')->form();

        // set some values
        $form['mealmatch_apibundle_meal_homemeal_tab_one[title]'] = 'TestMeal-Scenario2';
        $form['mealmatch_apibundle_meal_homemeal_tab_one[mealMain]'] = 'TestMeal-Scenario2-Hauptgang';
        $form['mealmatch_apibundle_meal_homemeal_tab_one[mealDesert]'] = 'TestMeal-Scenario2-Nachtisch';
        $form['mealmatch_apibundle_meal_homemeal_tab_one[mealStarter]'] = 'TestMeal-Scenario2-Vorspeise';
        $form['mealmatch_apibundle_meal_homemeal_tab_one[description]'] = 'TestMeal-Scenario2-Beschreibung';
//         submit the form
        $crawler = $this->client->submit($form);

        // we need a redirect ...


//        $mealId =  preg_replace('![^0-9]!', '', $crawler->getUri());

        $this->assertTrue(
            $this->client->getResponse()->isRedirect(),
            $this->client->getResponse()->getContent()
        );
//         follow the redirect ..
        $crawler = $this->client->followRedirect();
//         assert Response is ok
        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            $this->client->getResponse()->getContent()
        );
        // assert that we posted TAB1
        $form = $crawler->filter('form[name=mealmatch_apibundle_meal_homemeal_tab_one]')->form();
        $this->assertContains('TestMeal-Scenario2',
            $form['mealmatch_apibundle_meal_homemeal_tab_one[title]']->getValue()
        );
        $this->assertContains('TestMeal-Scenario2-Hauptgang',
            $form['mealmatch_apibundle_meal_homemeal_tab_one[mealMain]']->getValue()
        );
        $this->assertContains('TestMeal-Scenario2-Nachtisch',
            $form['mealmatch_apibundle_meal_homemeal_tab_one[mealDesert]']->getValue()
        );
        $this->assertContains('TestMeal-Scenario2-Vorspeise',
            $form['mealmatch_apibundle_meal_homemeal_tab_one[mealStarter]']->getValue()
        );
        $this->assertContains('TestMeal-Scenario2-Beschreibung',
            $form['mealmatch_apibundle_meal_homemeal_tab_one[description]']->getValue()
        );


        // Testing TAB2
//        $mealId =  preg_replace('![^0-9]!', '', $crawler->getUri());
//
//        $crawler = $this->client->request('GET', '/api/homemeal/manager/'.$mealId.'/edit');


        $link = $crawler->selectLink('Gäste | Kosten | Kategorien')->link();
        $crawler = $this->client->click($link);

        $form = $crawler->filter('form[name=mealmatch_apibundle_meal_homemeal_tab_two]')->form();
        $form['mealmatch_apibundle_meal_homemeal_tab_two[maxNumberOfGuest]']->setValue(10);
        $form['mealmatch_apibundle_meal_homemeal_tab_two[sharedCost]']->setValue('17.17');
        $form['mealmatch_apibundle_meal_homemeal_tab_two[sharedCostCurrency]']->setValue('EUR');
        $form['mealmatch_apibundle_meal_homemeal_tab_two[categories]']->setValue(2); // 2 = Vegan
        $form['mealmatch_apibundle_meal_homemeal_tab_two[countryCategory]']->setValue('DE');

        $crawler = $this->client->submit($form);

        $this->assertTrue(
            $this->client->getResponse()->isRedirect(),
            $this->client->getResponse()->getContent()
        );
//         follow the redirect ..
        $crawler = $this->client->followRedirect();
//         assert Response is ok
        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            $this->client->getResponse()->getContent()
        );

        $this->assertContains('10',
            $form['mealmatch_apibundle_meal_homemeal_tab_two[maxNumberOfGuest]']->getValue()
        );
        $this->assertContains('17.17',
            $form['mealmatch_apibundle_meal_homemeal_tab_two[sharedCost]']->getValue()
        );
        $this->assertContains('EUR',
            $form['mealmatch_apibundle_meal_homemeal_tab_two[sharedCostCurrency]']->getValue()
        );
        $this->assertContains(2,
            $form['mealmatch_apibundle_meal_homemeal_tab_two[categories]']->getValue()
        );
        $this->assertContains('DE',
            $form['mealmatch_apibundle_meal_homemeal_tab_two[countryCategory]']->getValue()
        );

        // TESTING TAB3
//        $mealId =  preg_replace('![^0-9]!', '', $crawler->getUri());
//
//        $crawler = $this->client->request('GET', '/api/homemeal/manager/'.$mealId.'/edit');

        $link = $crawler->selectLink('Ort | Adresse')->link();
        $crawler = $this->client->click($link);


        $form = $crawler->filter('form[name=mealmatch_apibundle_meal_mealaddress]')->form();
        $form['mealmatch_apibundle_meal_mealaddress[locationString]'] = 'Essen Am Parkfriedhof 2';
        $form['mealmatch_apibundle_meal_mealaddress[bellSign]'] = 'Meier';
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

        //assert that address is in form

        $this->assertGreaterThan(0, $crawler->filter('html:contains("Am Parkfriedhof")')->count()
        );
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Meier")')->count()
        );

        // TestingTab 4

        $link = $crawler->selectLink('Zeiträume')->link();
        $crawler = $this->client->click($link);

        $form = $crawler->filter('form[name=mealmatch_apibundle_meal_mealevent]')->form();
        $startDateTime = new DateTime('now');
        $endDateTime = new DateTime('+6 hours');
        $form['mealmatch_apibundle_meal_mealevent[startDateTime]'] = $startDateTime->format('d.m.Y H:i');
        $form['mealmatch_apibundle_meal_mealevent[endDateTime]'] = $endDateTime->format('d.m.Y H:i');
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


        $this->assertContains($startDateTime->format('d.m.Y H:i'),
            $form['mealmatch_apibundle_meal_mealevent[startDateTime]']->getValue()
        );
        $this->assertContains($endDateTime->format('d.m.Y H:i'),
            $form['mealmatch_apibundle_meal_mealevent[endDateTime]']->getValue()
        );

        // Create our meal
        $link = $crawler->selectLink('Erstellen')->link();
        $crawler = $this->client->click($link);

        $this->assertTrue(
            $this->client->getResponse()->isRedirect(),
            $this->client->getResponse()->getContent()
        );

        $crawler = $this->client->followRedirect();

        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            $this->client->getResponse()->getContent()
        );
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Veröffentlichen")')->count()
        );

        // Make meal public

        // Maybe we should use the crawler to pick the right meal to publish but for now we simple generate the URL
//        $link = $crawler->selectLink('Veröffentlichen')->link();
//        $crawler = $this->client->click($link);

        $crawler = $this->client->request('GET', '/api/workflow/doTransition/Meal/' . $mealId . '/start_meal');

        $this->assertTrue(
            $this->client->getResponse()->isRedirect(),
            $this->client->getResponse()->getContent()
        );

        $crawler = $this->client->followRedirect();

        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            $this->client->getResponse()->getContent()
        );

        return $mealId;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param        $crawler
     */
    private function deleteAllMyMeals($crawler)
    {
        /* No delete function in our form. But in pipelines the whole database is created from scratch every run,
        so it shouldn't matter. */
        static::markTestSkipped('We have no delete function in out form. Test needs re-write!');
        // Delete All Meals
        /** @noinspection PhpUndefinedMethodInspection */
        $allLinks = $crawler->filter('a')->extract(['name', 'href']);
        foreach ($allLinks as $link) {
            if ($link[0] === 'meal_edit_link') {
                $crawler = $this->client->request('GET', $link[1]);
                $form = $crawler->selectButton('meal_delete')->form();
                $crawler = $this->client->submit($form);
                $crawler = $this->client->followRedirect();
            }
        }
    }

    /**
     * @depends testApiHomeMealNewScenario1
     */
    public function testApiMealEditScenario1($mealId)
    {
        // This case is in principle unnecessary because we have standard entries and edit even if we create a new meal.
        // So i deactivate this method to use doctrine test bundle which reverts db changes for every case.
        static::markTestSkipped('We have no delete function in out form. Test needs re-write!');

        $crawler = $this->client->request('GET', '/api/workflow/doTransition/Meal/' . $mealId . '/stop_meal');

        $this->assertTrue(
            $this->client->getResponse()->isRedirect(),
            $this->client->getResponse()->getContent()
        );

        $crawler = $this->client->followRedirect();

        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            $this->client->getResponse()->getContent()
        );

        $crawler = $this->client->request('GET', '/api/homemeal/manager/' . $mealId . '/edit');
        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            $this->client->getResponse()->getContent()
        );


        $form = $crawler->filter('form[name=mealmatch_apibundle_meal_homemeal_tab_one]')->form();

        // set some values
        $form['mealmatch_apibundle_meal_homemeal_tab_one[title]'] = 'TestMeal-Scenario2Edit';
        $form['mealmatch_apibundle_meal_homemeal_tab_one[mealMain]'] = 'TestMeal-Scenario2-HauptgangEdit';
        $form['mealmatch_apibundle_meal_homemeal_tab_one[mealDesert]'] = 'TestMeal-Scenario2-NachtischEdit';
        $form['mealmatch_apibundle_meal_homemeal_tab_one[mealStarter]'] = 'TestMeal-Scenario2-VorspeiseEdit';
        $form['mealmatch_apibundle_meal_homemeal_tab_one[description]'] = 'TestMeal-Scenario2-BeschreibungEdit';
//         submit the form
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


        $form = $crawler->filter('form[name=mealmatch_apibundle_meal_homemeal_tab_one]')->form();
        $this->assertContains('TestMeal-Scenario2Edit',
            $form['mealmatch_apibundle_meal_homemeal_tab_one[title]']->getValue()
        );
        $this->assertContains('TestMeal-Scenario2-HauptgangEdit',
            $form['mealmatch_apibundle_meal_homemeal_tab_one[mealMain]']->getValue()
        );
        $this->assertContains('TestMeal-Scenario2-NachtischEdit',
            $form['mealmatch_apibundle_meal_homemeal_tab_one[mealDesert]']->getValue()
        );
        $this->assertContains('TestMeal-Scenario2-VorspeiseEdit',
            $form['mealmatch_apibundle_meal_homemeal_tab_one[mealStarter]']->getValue()
        );
        $this->assertContains('TestMeal-Scenario2-BeschreibungEdit',
            $form['mealmatch_apibundle_meal_homemeal_tab_one[description]']->getValue()
        );

        $crawler = $this->client->request('GET', '/api/workflow/doTransition/Meal/' . $mealId . '/restart_meal');

        $this->assertTrue(
            $this->client->getResponse()->isRedirect(),
            $this->client->getResponse()->getContent()
        );

        $crawler = $this->client->followRedirect();

        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            $this->client->getResponse()->getContent()
        );

        /* No delete function in our form. But in pipelines the whole database is created from scratch every run,
        so it shouldn't matter. */
        //$this->deleteAllMyMeals($crawler);
    }

    /*
    public function testCompleteScenario()
    {
        // Create a new client to browse the application
        $client = static::createClient();

        // Create a new entry in the database
        $crawler = $client->request('GET', '/meal/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /meal/");
        $crawler = $client->click($crawler->selectLink('Create a new entry')->link());

        // Fill in the form and submit it
        $form = $crawler->selectButton('Create')->form(array(
            'mmapibundle_meal[field_name]'  => 'ServiceDataManagerTest',
            // ... other fields to fill
        ));

        $client->submit($form);
        $crawler = $client->followRedirect();

        // Check data in the show view
        $this->assertGreaterThan(0, $crawler->filter('td:contains("ServiceDataManagerTest")')->count(),
    'Missing element td:contains("ServiceDataManagerTest")');

        // Edit the entity
        $crawler = $client->click($crawler->selectLink('Edit')->link());

        $form = $crawler->selectButton('Update')->form(array(
            'mmapibundle_meal[field_name]'  => 'Foo',
            // ... other fields to fill
        ));

        $client->submit($form);
        $crawler = $client->followRedirect();

        // Check the element contains an attribute with value equals "Foo"
        $this->assertGreaterThan(0, $crawler->filter('[value="Foo"]')->count(), 'Missing element [value="Foo"]');

        // Delete the entity
        $client->submit($crawler->selectButton('Delete')->form());
        $crawler = $client->followRedirect();

        // Check the entity has been delete on the list
        $this->assertNotRegExp('/Foo/', $client->getResponse()->getContent());
    }

    */

    public function testApiMealDeleteScenario1()
    {
        /* No delete function in our form. But in pipelines the whole database is created from scratch every run,
        so it shouldn't matter. */
        static::markTestSkipped('We have no delete function in out form. Test needs re-write!');

        $crawler = $this->client->request('GET', '/api/meal/new');

        $form = $crawler->selectButton('Meal hinzufügen und publizieren')->form();

        // set some values
        $form['mmapibundle_meal[title]'] = 'SocialTestMealFromUnitTest';
        $form['mmapibundle_meal[main]'] = 'SocialTestNudelnMain';
        $form['mmapibundle_meal[sharedCost]'] = '0';
        $form['mmapibundle_meal[locationAddress]'] = 'Petersburgerstraße 69, Berlin';
        $form['mmapibundle_meal[startDateTime]'] = '4.2.4242 16:20';
        // submit the form
        $crawler = $this->client->submit($form);
        $crawler = $this->client->followRedirect();
        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            $this->client->getResponse()->getContent()
        );

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("TestMealFromUnitTest")')->count()
        );

        $this->deleteAllMyMeals($crawler);
    }

    protected function getLinksOnCurrentPage(Crawler $crawler, $name = 'foo')
    {

        $links = $crawler->filter('a')->each(
            function (Crawler $node, $name) {
                if ($node->extract(['name']) === $name) {
                    return $node->link()->getUri();
                }

                return [];
            }
        );

        return array_values($links);
    }
}

