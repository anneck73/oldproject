<?php
/**
 * Created by PhpStorm.
 * User: markus
 * Date: 14.12.18
 * Time: 16:26
 */

namespace Mealmatch\ApiBundle\Controller\Meal;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Client;


class MealControllerAsRestaurantUserTest extends WebTestCase
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
            'PHP_AUTH_USER' => 'MMTestRestaurant',
            'PHP_AUTH_PW' => 'MMTest',
        ));

    }

    public function testIndexAction()
    {
        $crawler = $this->client->request('GET', '/api/meal/');

        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            'Meal Index Failed: ' . $this->client->getResponse()->getStatusCode()
        );
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Deine Restaurant-Meals verwalten")')->count());

    }

    public function testApiRestaurantMeal()
    {
        $crawler = $this->client->request('GET', '/api/promeal/new');

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
            $crawler->filter('html:contains("Meal Manager")')->count()
        );

        // We need the id of the meal to publish it later
        $mealId = preg_replace('![^0-9]!', '', $crawler->getUri());
        // Since we only making offers public we need to increase id + 1
        ++$mealId;

        $link = $crawler->selectLink('Meal')->link();
        $crawler = $this->client->click($link);

        // TESTING TAB1
        $form = $crawler->filter('form[name=mealmatch_apibundle_meal_promeal]')->form();

        // set some values
        $form['mealmatch_apibundle_meal_promeal[title]'] = 'TestRestaurantMeal';
        $form['mealmatch_apibundle_meal_promeal[tableTopic]'] = 'TestRestaurantMeal-TableTopic';
        $form['mealmatch_apibundle_meal_promeal[maxNumberOfGuest]']->setValue(10);
        $form['mealmatch_apibundle_meal_promeal[specials]'] = 'TestRestaurantMeal-Besonderheiten';
        $form['mealmatch_apibundle_meal_promeal[description]'] = 'TestRestaurantMeal-Beschreibung';
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
        $form = $crawler->filter('form[name=mealmatch_apibundle_meal_promeal]')->form();
        $this->assertContains('TestRestaurantMeal',
            $form['mealmatch_apibundle_meal_promeal[title]']->getValue()
        );
        $this->assertContains('TestRestaurantMeal-TableTopic',
            $form['mealmatch_apibundle_meal_promeal[tableTopic]']->getValue()
        );
        $this->assertContains('10',
            $form['mealmatch_apibundle_meal_promeal[maxNumberOfGuest]']->getValue()
        );
        $this->assertContains('TestRestaurantMeal-Besonderheiten',
            $form['mealmatch_apibundle_meal_promeal[specials]']->getValue()
        );
        $this->assertContains('TestRestaurantMeal-Beschreibung',
            $form['mealmatch_apibundle_meal_promeal[description]']->getValue()
        );

        // Testing TAB2
//        $mealId =  preg_replace('![^0-9]!', '', $crawler->getUri());
//
//        $crawler = $this->client->request('GET', '/api/homemeal/manager/'.$mealId.'/edit');


        $link = $crawler->selectLink('Angebote')->link();
        $crawler = $this->client->click($link);


        $form = $crawler->filter('form[name=mealmatch_apibundle_meal_mealoffer]')->form();
        $form['mealmatch_apibundle_meal_mealoffer[name]'] = 'Mealmatch-Burger';
        $form['mealmatch_apibundle_meal_mealoffer[description]'] = 'Jetzt mit noch mehr Zwiebeln';
        $form['mealmatch_apibundle_meal_mealoffer[price]']->setValue(15.23);

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

        $this->assertContains('Mealmatch-Burger',
            $form['mealmatch_apibundle_meal_mealoffer[name]']->getValue()
        );
        $this->assertContains('Jetzt mit noch mehr Zwiebeln',
            $form['mealmatch_apibundle_meal_mealoffer[description]']->getValue()
        );
        $this->assertContains('15.23',
            $form['mealmatch_apibundle_meal_mealoffer[price]']->getValue()
        );

        $link = $crawler->selectLink('Kennzeichnung')->link();
        $crawler = $this->client->click($link);

        $form = $crawler->filter('[name=mealmatch_apibundle_meal_promeal_notes]')->form();

        $form['mealmatch_apibundle_meal_promeal_notes[mealOfferNotes]']->setValue('Mit Käse');
        $form['mealmatch_apibundle_meal_promeal_notes[countryOfferNotes]']->setValue('Zwiebeln');
        $form['mealmatch_apibundle_meal_promeal_notes[categories]'][0]->untick();
        $form['mealmatch_apibundle_meal_promeal_notes[categories]'][3]->tick();

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

        // Check if Value are equal

        $this->assertContains('Mit Käse',
            $form['mealmatch_apibundle_meal_promeal_notes[mealOfferNotes]']->getValue()
        );

        // Why is this not shown when looking to the meal with the browser, it stays on -DEFAULT-
        // If changed manually in  the browser the value is taken... strange
        $this->assertContains('Zwiebeln',
            $form['mealmatch_apibundle_meal_promeal_notes[countryOfferNotes]']->getValue()
        );

        // Hmm... looks it works but what does $vegetarisch return... must be false !?
        $vegetarisch = $crawler->filter('#mealmatch_apibundle_meal_promeal_notes_categories_1');
        $this->assertNotContains('input[checked=checked]',
            $vegetarisch->filter('input[checked=checked]')
        );

        $fleischgericht = $crawler->filter('#mealmatch_apibundle_meal_promeal_notes_categories_4');
        $this->assertGreaterThan(0,
            $fleischgericht->filter('input[checked=checked]')->count()
    );
        // TestingTab 4

        $link = $crawler->selectLink('Zeiträume')->link();
        $crawler = $this->client->click($link);

        $form = $crawler->filter('form[name=mealmatch_apibundle_meal_mealevent]')->form();
        $startDateTime = new DateTime('+4 hours');
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
    }
}
