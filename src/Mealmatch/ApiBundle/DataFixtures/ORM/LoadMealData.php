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
use Mealmatch\ApiBundle\MealMatch\UserManager;
use MMUserBundle\Entity\MMUser;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadMealData implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
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
        $meal->setTitle('TestHomeMeal-Scenario1 und ein viel zu langer Titel der dennoch gut dargestellt wird.');
        $meal->setDescription('TestHomeMeal-Scenario1 Beschreibung.... ');
        $meal->setMealMain('Nudeln mit Tomatensoße');
        $meal->setMealStarter('Suppe');
        $meal->setMealDesert('Eiscreme');
        $meal->setCreatedBy($mmTestHost);
        $meal->setHost($mmTestHost);
        $meal->setMaxNumberOfGuest(10);
        $meal->setSharedCost('5.50');
        $meal->addCategory($cat1);

        $mealEvent = new MealEvent();
        $mealEvent->setStartDateTime(new DateTime('now'));
        $mealEvent->setEndDateTime(new DateTime('+6 hours'));
        $mealEvent->setReoccuring(true);
        $mealEvent->setRrule('FREQ=DAILY;COUNT=5');
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
