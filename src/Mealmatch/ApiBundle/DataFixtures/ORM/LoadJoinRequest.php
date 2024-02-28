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
use Mealmatch\ApiBundle\Entity\Meal\MealJoinRequest;
use Mealmatch\ApiBundle\MealMatch\UserManager;
use MMUserBundle\Entity\MMUser;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadJoinRequest implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
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

        /** @var MMUser $mmTestGuest */
        $mmTestGuest = $userManager->findUserByUsername('MMTestGuest');

        /** @var EntityManager $entityManager */
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        $cat1 = $entityManager->getRepository('ApiBundle:Meal\BaseMealCategory')->findAll()[0];

        $meal1 = new HomeMeal();
        $meal1->setTitle('Dinner in the Dark');
        $meal1->setDescription('Du bist ein wahrer Feinschmecker und selbsternannter Gourmet? Dann kannst Du Dich beim Dinner in the Dark in Essen einmal so richtig überraschen lassen. ');
        $meal1->setMealMain('4-Gänge-Menü');
        $meal1->setMealStarter('Suppe');
        $meal1->setMealDesert('Eiscreme');
        $meal1->setCreatedBy($mmTestHost);
        $meal1->setHost($mmTestHost);
        $meal1->setMaxNumberOfGuest(10);
        $meal1->setSharedCost('60');
        $meal1->addCategory($cat1);

        $mealJoinRequest1 = new MealJoinRequest();
        $mealJoinRequest1->setBaseMeal($meal1);
        $mealJoinRequest1->setAccepted(true);
        $mealJoinRequest1->setMessageToHost('Ich würde gerne beim Dinner in the Dark teilnehmen');
        $mealJoinRequest1->setMessageToGuest('Sehr gerne!');
        $mealJoinRequest1->setStatus(ApiConstants::JOIN_REQ_STATUS_PAYED);
        $mealJoinRequest1->setCreatedBy($mmTestGuest);
        $mealJoinRequest1->setCreatedAt(new DateTime('now'));

        $meal1->addJoinRequest($mealJoinRequest1);

        $mealEvent1 = new MealEvent();
        $mealEvent1->setStartDateTime(new DateTime('now'));
        $mealEvent1->setEndDateTime(new DateTime('+6 hours'));
        $mealEvent1->setReoccuring(true);
        $mealEvent1->setRrule('FREQ=DAILY;COUNT=1');
        $meal1->addMealEvent($mealEvent1);

        $meal1->setSharedCostCurrency('EU');
        $meal1->setStatus(ApiConstants::MEAL_STATUS_RUNNING);

        $addressEntity1 = $this->container->get('api.geo_address.service')->createMealAddressByLocation(
            'Steinhausenstraße 26, 45147 Essen'
        )->getMealAddress();

        $meal1 = $meal1->addMealAddress($addressEntity1);

        // $manager->persist($meal1);
        $serviceData = $this->container->get('api.home_meal.service')->createFromEntity($meal1);
        $createdRootMeal = $serviceData->getHomeMeal();
        $this->container->get('api.meal.service')
            ->createAllHomeMealEvents($createdRootMeal, ApiConstants::MEAL_STATUS_RUNNING);

        /*
         * *********************************************************************************************************** */

        $meal2 = new HomeMeal();
        $meal2->setTitle('Ritteressen');
        $meal2->setDescription('Du bist ein Liebhaber von Rittern, Prinzessinnen, Schlössern und Burgen? Du besuchst gerne Mittelalterfeste, um selbst die Atmosphäre der damaligen Gesellschaft hautnah zu erleben? ');
        $meal2->setMealMain('Wildschwein');
        $meal2->setMealStarter('Ritter-Suppe');
        $meal2->setMealDesert('Obstsalat');
        $meal2->setCreatedBy($mmTestHost);
        $meal2->setHost($mmTestHost);
        $meal2->setMaxNumberOfGuest(10);
        $meal2->setSharedCost('50');
        $meal2->addCategory($cat1);

        $mealJoinRequest2 = new MealJoinRequest();
        $mealJoinRequest2->setBaseMeal($meal2);
        $mealJoinRequest2->setAccepted(true);
        $mealJoinRequest2->setMessageToHost('Ich würde gerne beim Ritteressen teilnehmen');
        $mealJoinRequest2->setMessageToGuest('Sehr gerne!');
        $mealJoinRequest2->setStatus(ApiConstants::JOIN_REQ_STATUS_CREATED);
        $mealJoinRequest2->setCreatedBy($mmTestGuest);
        $mealJoinRequest2->setCreatedAt(new DateTime('now'));

        $meal2->addJoinRequest($mealJoinRequest2);

        $mealEvent2 = new MealEvent();
        $mealEvent2->setStartDateTime(new DateTime('now'));
        $mealEvent2->setEndDateTime(new DateTime('+6 hours'));
        $mealEvent2->setReoccuring(true);
        $mealEvent2->setRrule('FREQ=WEEKLY;COUNT=1');
        $meal2->addMealEvent($mealEvent2);

        $meal2->setSharedCostCurrency('EU');
        $meal2->setStatus(ApiConstants::MEAL_STATUS_RUNNING);

        $addressEntity2 = $this->container->get('api.geo_address.service')->createMealAddressByLocation(
            'Steinhausenstraße 26, 45147 Essen'
        )->getMealAddress();

        $meal2 = $meal2->addMealAddress($addressEntity2);

        // $manager->persist($meal1);
        $serviceData = $this->container->get('api.home_meal.service')->createFromEntity($meal2);
        $createdRootMeal = $serviceData->getHomeMeal();
        $this->container->get('api.meal.service')
            ->createAllHomeMealEvents($createdRootMeal, ApiConstants::MEAL_STATUS_RUNNING);

        /*
             * *********************************************************************************************************** */

        $meal3 = new HomeMeal();
        $meal3->setTitle('Chilliessen');
        $meal3->setDescription(
            'Es gibt Hunderte von Chili-Sorten – von superfeurig bis hin zu milder Schärfe. Eines haben sie aber alle gemeinsam: Sie tun unserer Gesundheit gut.'
        );
        $meal3->setMealMain('Chili');
        $meal3->setMealStarter('Chili-Suppe');
        $meal3->setMealDesert('Chilisalat');
        $meal3->setCreatedBy($mmTestHost);
        $meal3->setHost($mmTestHost);
        $meal3->setMaxNumberOfGuest(10);
        $meal3->setSharedCost('50');
        $meal3->addCategory($cat1);

        $mealJoinRequest3 = new MealJoinRequest();
        $mealJoinRequest3->setBaseMeal($meal3);
        $mealJoinRequest3->setAccepted(true);
        $mealJoinRequest3->setMessageToHost('Ich würde gerne beim Chilliessen teilnehmen');
        $mealJoinRequest3->setMessageToGuest('Sehr gerne!');
        $mealJoinRequest3->setStatus(ApiConstants::JOIN_REQ_STATUS_ACCEPTED);
        $mealJoinRequest3->setCreatedBy($mmTestGuest);
        $mealJoinRequest3->setCreatedAt(new DateTime('now'));

        $meal3->addJoinRequest($mealJoinRequest3);

        $mealEvent3 = new MealEvent();
        $mealEvent3->setStartDateTime(new DateTime('now'));
        $mealEvent3->setEndDateTime(new DateTime('+6 hours'));
        $mealEvent3->setReoccuring(true);
        $mealEvent3->setRrule('FREQ=WEEKLY;COUNT=1');
        $meal3->addMealEvent($mealEvent3);

        $meal3->setSharedCostCurrency('EU');
        $meal3->setStatus(ApiConstants::MEAL_STATUS_RUNNING);

        $addressEntity3 = $this->container->get('api.geo_address.service')->createMealAddressByLocation(
            'Steinhausenstraße 26, 45147 Essen'
        )->getMealAddress();

        $meal3 = $meal3->addMealAddress($addressEntity3);

        // $manager->persist($meal1);
        $serviceData = $this->container->get('api.home_meal.service')->createFromEntity($meal3);
        $createdRootMeal = $serviceData->getHomeMeal();
        $this->container->get('api.meal.service')
            ->createAllHomeMealEvents($createdRootMeal, ApiConstants::MEAL_STATUS_RUNNING);

        /*
             * *********************************************************************************************************** */

        $meal4 = new HomeMeal();
        $meal4->setTitle('Gurkenessen');
        $meal4->setDescription(
            'Es gibt Hunderte von Gurken-Sorten – von superfeurig bis hin zu milder Schärfe. Eines haben sie aber alle gemeinsam: Sie tun unserer Gesundheit gut.'
        );
        $meal4->setMealMain('Gurke');
        $meal4->setMealStarter('Gurken-Suppe');
        $meal4->setMealDesert('Gurkensalat');
        $meal4->setCreatedBy($mmTestHost);
        $meal4->setHost($mmTestHost);
        $meal4->setMaxNumberOfGuest(10);
        $meal4->setSharedCost('50');
        $meal4->addCategory($cat1);

        $mealJoinRequest4 = new MealJoinRequest();
        $mealJoinRequest4->setBaseMeal($meal4);
        $mealJoinRequest4->setAccepted(true);
        $mealJoinRequest4->setMessageToHost('Ich würde gerne beim Gurkenessen teilnehmen');
        $mealJoinRequest4->setMessageToGuest('Niemals!');
        $mealJoinRequest4->setStatus(ApiConstants::JOIN_REQ_STATUS_DENIED);
        $mealJoinRequest4->setCreatedBy($mmTestGuest);
        $mealJoinRequest4->setCreatedAt(new DateTime('now'));

        $meal4->addJoinRequest($mealJoinRequest4);

        $mealEvent4 = new MealEvent();
        $mealEvent4->setStartDateTime(new DateTime('now'));
        $mealEvent4->setEndDateTime(new DateTime('+6 hours'));
        $mealEvent4->setReoccuring(true);
        $mealEvent4->setRrule('FREQ=WEEKLY;COUNT=1');
        $meal4->addMealEvent($mealEvent4);

        $meal4->setSharedCostCurrency('EU');
        $meal4->setStatus(ApiConstants::MEAL_STATUS_RUNNING);

        $addressEntity4 = $this->container->get('api.geo_address.service')->createMealAddressByLocation(
            'Steinhausenstraße 26, 45147 Essen'
        )->getMealAddress();

        $meal4 = $meal4->addMealAddress($addressEntity4);

        // $manager->persist($meal1);
        $serviceData = $this->container->get('api.home_meal.service')->createFromEntity($meal4);
        $createdRootMeal = $serviceData->getHomeMeal();
        $this->container->get('api.meal.service')
            ->createAllHomeMealEvents($createdRootMeal, ApiConstants::MEAL_STATUS_RUNNING);

        /*
                 * *********************************************************************************************************** */

        $meal5 = new HomeMeal();
        $meal5->setTitle('Kartoffelessen');
        $meal5->setDescription(
            'Es gibt Hunderte von Kartoffel-Sorten – von superfeurig bis hin zu milder Schärfe. Eines haben sie aber alle gemeinsam: Sie tun unserer Gesundheit gut.'
        );
        $meal5->setMealMain('Bratkartoffeln');
        $meal5->setMealStarter('Kartoffel-Suppe');
        $meal5->setMealDesert('Kartoffelsalat');
        $meal5->setCreatedBy($mmTestHost);
        $meal5->setHost($mmTestHost);
        $meal5->setMaxNumberOfGuest(10);
        $meal5->setSharedCost('50');
        $meal5->addCategory($cat1);

        $mealJoinRequest5 = new MealJoinRequest();
        $mealJoinRequest5->setBaseMeal($meal5);
        $mealJoinRequest5->setAccepted(true);
        $mealJoinRequest5->setMessageToHost('Ich würde gerne beim Kartoffelessen teilnehmen');
        $mealJoinRequest5->setMessageToGuest('Sehr gerne!');
        $mealJoinRequest5->setStatus(ApiConstants::JOIN_REQ_STATUS_PAYMENT_FAILED);
        $mealJoinRequest5->setCreatedBy($mmTestGuest);
        $mealJoinRequest5->setCreatedAt(new DateTime('now'));

        $meal5->addJoinRequest($mealJoinRequest5);

        $mealEvent5 = new MealEvent();
        $mealEvent5->setStartDateTime(new DateTime('now'));
        $mealEvent5->setEndDateTime(new DateTime('+6 hours'));
        $mealEvent5->setReoccuring(true);
        $mealEvent5->setRrule('FREQ=WEEKLY;COUNT=1');
        $meal5->addMealEvent($mealEvent5);

        $meal5->setSharedCostCurrency('EU');
        $meal5->setStatus(ApiConstants::MEAL_STATUS_RUNNING);

        $addressEntity5 = $this->container->get('api.geo_address.service')->createMealAddressByLocation(
            'Steinhausenstraße 26, 45147 Essen'
        )->getMealAddress();

        $meal5 = $meal5->addMealAddress($addressEntity5);

        // $manager->persist($meal1);
        $serviceData = $this->container->get('api.home_meal.service')->createFromEntity($meal5);
        $createdRootMeal = $serviceData->getHomeMeal();
        $this->container->get('api.meal.service')
            ->createAllHomeMealEvents($createdRootMeal, ApiConstants::MEAL_STATUS_RUNNING);
    }

    public function getOrder()
    {
        return 50;
    }
}
