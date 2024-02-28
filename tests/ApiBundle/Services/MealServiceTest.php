<?php
/**
 * Copyright 2016-2017 MealMatch UG
 *
 * Author: Wizard <wizard@mealmatch.de>
 * Created: 01.02.18 11:43
 */

namespace Tests\ApiBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Mealmatch\ApiBundle\Entity\Meal\BaseMeal;
use Mealmatch\ApiBundle\Entity\Meal\MealEvent;
use Mealmatch\ApiBundle\Services\MealService;
use Mealmatch\MealmatchKernelTestCase;

class MealServiceTest extends MealmatchKernelTestCase
{

    public function testFindFinishedMeals()
    {
        $mealService = static::$kernel->getContainer()->get('api.meal.service');
        $finishedMeals = $mealService->findFinishedMeals();
//        Ist darauf angewiesen das vorher setupDB.sh aufgerufen wurde
        self::assertEquals(1, $finishedMeals->count());
// @todo So umschreiben das der Testcase selber dafür sorgt ein Meals selber zu finishen,
    }

    public function testFindAll()
    {
        $mealService = static::$kernel->getContainer()->get('api.meal.service');
        $allMealsArray = $mealService->findAll();
        $allMeals = new ArrayCollection($allMealsArray);
        self::assertEquals(18, $allMeals->count());
        // @todo, find more ways to test based on loadTestData.

// @todo Dafür sorgen das vielleicht nicht genau die 18 Datensätze benötigt werden
    }

    public function testIsGuest()
    {
        $mealService = static::$kernel->getContainer()->get('api.meal.service');
        $userManager = static::$kernel->getContainer()->get('api.user_manager');

        $baseMeal = new BaseMeal();
        $baseMeal->setTitle('Foo');
        $mealEvent = new MealEvent();
        $mealEvent->setStartDateTime(new \DateTime('+3 days'));
        $baseMeal->setMealEvents(new ArrayCollection(array($mealEvent)));

        $baseMeal->setStatus('Testing');
        $baseMeal->setDescription('Description');

        $this->logIn('MMTestGuest');
        $user = $this->em->getRepository('MMUserBundle:MMUser')->findOneByUsername('MMTestGuest');

        self::assertFalse($mealService->isGuest($baseMeal, $user));

        $baseMeal->addGuest($user);

        $this->em->persist($baseMeal);

        try {
            $this->em->flush();
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }

        self::assertTrue($mealService->isGuest($baseMeal, $user));

        $this->em->remove($baseMeal);

        try {
            $this->em->flush();
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }

    }

    public function testJoinMeal()
    {
        // @todo Sollte mittlerweile einfch zu schreiben sein. WICHTIG ... mehr als die folgenden.
        self::markTestSkipped();
        self::fail('Has not been implemented yet!');
    }

    public function testCreateAllProMealEvents()
    {

        self::markTestSkipped();
        self::fail('Has not been implemented yet!');
    }

    public function testCreateAllHomeMealEvents()
    {
        self::markTestSkipped();
        self::fail('Has not been implemented yet!');
    }

    public function testAddOfferToProMeal()
    {
        self::markTestSkipped();
        self::fail('Has not been implemented yet!');
    }

    public function testRemoveOfferFromProMeal()
    {
        self::markTestSkipped();
        self::fail('Has not been implemented yet!');
    }



    public function testAddEventToMeal()
    {
        self::markTestSkipped();
        self::fail('Has not been implemented yet!');
    }

    public function testRemoveEventFromMeal()
    {
        self::markTestSkipped();
        self::fail('Has not been implemented yet!');
    }




    public function testSetMealStatus()
    {
//        @todo Für jeden Status ein Test Evtl. in eigene TestKlasse auslagern
        self::markTestSkipped();
        self::fail('Has not been implemented yet!');
    }



    public function testGetRunningByUser()
    {
//        @todo Entweder auf vorhanden Datensätzen prüfen oder entsprechend vorbereiten. MealService
        self::markTestSkipped();
        self::fail('Has not been implemented yet!');
    }

    public function testGetJoinedByUser()
    {
        //        @todo Entweder auf vorhanden Datensätzen prüfen oder entsprechend vorbereiten. MealService
        self::markTestSkipped();
        self::fail('Has not been implemented yet!');
    }

    public function testIsUserGuestOfHost()
    {
        /** @var MealService $mealService */
        $mealService = static::$kernel->getContainer()->get('api.meal.service');
        $userManager = static::$kernel->getContainer()->get('api.user_manager');
        $testUser = $userManager->getMealmatchUser("MMTestGuest");
        $testHost = $userManager->getMealmatchUser("MMTestRestaurant");
        self::assertTrue($mealService->isUserGuestOfHost($testUser, $testHost));
    }
}
