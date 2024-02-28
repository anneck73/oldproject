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
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Entity\Meal\MealEvent;
use Mealmatch\ApiBundle\Entity\Meal\MealOffer;
use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Mealmatch\ApiBundle\MealMatch\UserManager;
use MMUserBundle\Entity\MMUser;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadProMealData implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
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
        $mmTestHost = $userManager->findUserByUsername('MMTestRestaurant');

        $proMealEntity = new ProMeal();

        $offer1 = new MealOffer();
        $offer1->setName('Jägerschnitzel mit Pommes + Getränk');
        $offer1->setDescription('Ein leckeres Jägerschnitzel, dazu ausreichend Pommes und ein Getränk nach Wahl.');
        $offer1->setAvailableAmount(15);
        $offer1->setPrice(10.00);
        $offer1->setCurrency('EUR');

        $offer2 = new MealOffer();
        $offer2->setName('Jägerschnitzel mit Pommes + Getränk und dazu noch viel zu viel Text der trotzdem in die Zeile passt und dazu noch ein Donaudampfschiffahrtsgeselschaftskapitän');
        $offer2->setDescription('Ein leckeres Jägerschnitzel, dazu ausreichend Pommes und ein Getränk nach Wahl.');
        $offer2->setAvailableAmount(10);
        $offer2->setPrice(15.00);
        $offer2->setCurrency('EUR');

        $proMealEntity->addMealOffer($offer1);
        $proMealEntity->addMealOffer($offer2);

        $mealEvent = new MealEvent();
        $mealEvent->setStartDateTime(new DateTime('now'));
        $mealEvent->setEndDateTime(new DateTime('+6 hours'));
        $mealEvent->setReoccuring(true);
        $mealEvent->setRrule('FREQ=DAILY;COUNT=5');
        $proMealEntity->addMealEvent($mealEvent);

        $proMealEntity->setMaxNumberOfGuest(10);
        $proMealEntity->setTitle('ProMeal Titel ...');
        $proMealEntity->setDescription('ProMeal Lauftext ...');
        $proMealEntity->setTableTopic('Pro Meal Table Topic1');
        $proMealEntity->setHost($mmTestHost);
        $proMealEntity->setSharedCost(9.99);

        $proMealEntity->setSharedCostCurrency('EUR');
        $proMealEntity->setStatus(ApiConstants::MEAL_STATUS_RUNNING);

        $geoAddressService = $this->container->get('api.geo_address.service');
        $serviceData = $geoAddressService->createMealAddressByLocation('Petersburgerstraße 69, Berlin');
        $mealAddress = $serviceData->getMealAddress();
        $proMealEntity->addMealAddress($mealAddress);

        $serviceData = $this->container->get('api.pro_meal.service')->createFromEntity($proMealEntity);
        $createdRootProMeal = $serviceData->getProMeal();
        $this->container->get('api.meal.service')
            ->createAllProMealEvents($createdRootProMeal, ApiConstants::MEAL_STATUS_RUNNING);

        // Created a ProMeal in the past <= today
        $proMealPast = new ProMeal();
        $proMealPast->addMealOffer($offer1);
        $proMealPast->addMealOffer($offer2);

        $pastMealEvent = new MealEvent();
        $pastMealEvent->setStartDateTime(new DateTime('yesterday'));
        $pastMealEvent->setEndDateTime(new DateTime('yesterday'));
        $pastMealEvent->setReoccuring(true);
        $pastMealEvent->setRrule('FREQ=DAILY;COUNT=5');
        $proMealPast->addMealEvent($pastMealEvent);

        $proMealPast->setMaxNumberOfGuest(10);
        $proMealPast->setTitle('ProMeal Titel ...');
        $proMealPast->setDescription('ProMeal Lauftext ...');
        $proMealPast->setTableTopic('Pro Meal Table Topic1');
        $proMealPast->setHost($mmTestHost);
        $proMealPast->setSharedCost(9.99);
        $proMealPast->setSharedCostCurrency('EUR');
        $proMealPast->setStatus(ApiConstants::MEAL_STATUS_FINISHED);
        $proMealPast->addMealAddress($mealAddress);

        $serviceDataPast = $this->container->get('api.pro_meal.service')->createFromEntity($proMealPast);
        $createdRootProMealPast = $serviceDataPast->getProMeal();
        $this->container->get('api.meal.service')
            ->createAllProMealEvents($createdRootProMealPast, ApiConstants::MEAL_STATUS_FINISHED);
    }

    public function getOrder()
    {
        return 40;
    }
}
