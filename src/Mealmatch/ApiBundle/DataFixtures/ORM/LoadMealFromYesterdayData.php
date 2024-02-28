<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\DataFixtures\ORM;

use DateTime;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Entity\Meal\HomeMeal;
use Mealmatch\ApiBundle\Entity\Meal\MealEvent;
use MMApiBundle\MealMatch\UserManager;
use MMUserBundle\Entity\MMUser;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadMealFromYesterdayData implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * @todo: Finish PHPDoc!
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        /** @var UserManager $userManager */
        $userManager = $this->container->get('api.user_manager');

        /** @var MMUser $mmTestHost */
        $mmTestHost = $userManager->findUserByUsername('MMTestHost');

        /** @var EntityManager $entityManager */
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        $cat1 = $entityManager->getRepository('ApiBundle:Meal\BaseMealCategory')->findAll()[0];

        $meal = new HomeMeal();
        $meal->setTitle('TestHomeMeal-Scenario2 (von Gestern)');
        $meal->setDescription('TestHomeMeal-Scenario2 Das ist eine Mealseria von gestern. ');
        $meal->setMealMain('Nudeln mit Gurkensuppe');
        $meal->setMealStarter('Käsehobel');
        $meal->setMealDesert('Schweinebraten');
        $meal->setCreatedBy($mmTestHost);
        $meal->setHost($mmTestHost);
        $meal->setMaxNumberOfGuest(7);
        $meal->setSharedCost('55.55');
        $meal->addCategory($cat1);

        $mealEvent = new MealEvent();
        $mealEvent->setStartDateTime(new DateTime('-2 days'));
        $mealEvent->setEndDateTime(new DateTime('+6 hours'));
        $mealEvent->setReoccuring(true);
        // This should make the 2nd meal appear in the search ...
        $mealEvent->setRrule('FREQ=DAILY;COUNT=2');

        $meal->addMealEvent($mealEvent);

        $meal->setSharedCostCurrency('EUR');
        $meal->setStatus(ApiConstants::MEAL_STATUS_RUNNING);

        $addressEntity = $this->container->get('api.geo_address.service')->createMealAddressByLocation(
            'Am Parkfriedhof 28, Essen'
        )->getMealAddress();

        $meal = $meal->addMealAddress($addressEntity);

        $serviceData = $this->container->get('api.home_meal.service')->createFromEntity($meal);
        $createdRootMeal = $serviceData->getHomeMeal();
        $this->container->get('api.meal.service')
            ->createAllHomeMealEvents($createdRootMeal, ApiConstants::MEAL_STATUS_RUNNING);
    }

    public function getOrder()
    {
        return 3;
    }
}
