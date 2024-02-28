<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\DataFixtures\ORM;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Mealmatch\ApiBundle\MealMatch\UserManager;
use MMUserBundle\Entity\MMUser;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @todo: Finish PHPDoc!
 * Create 2 MealTickets with status PAYED to show in RestaurantView.
 */
class LoadMealTicketData implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var UserManager $userManager */
        $userManager = $this->container->get('api.user_manager');

        /** @var MMUser $mmTestRestaurantUser */
        $mmTestRestaurantUser = $userManager->findUserByUsername('MMTestRestaurant');

        /** @var MMUser $mmTestGuest */
        $mmTestGuest = $userManager->findUserByUsername('MMTestGuest');
        $mmTestGuest2 = $userManager->findUserByUsername('MMTestGuest2');
        $criteria = new Criteria();
        $criteria->getFirstResult();

        $proMealService = $this->container->get('api.pro_meal.service');
        $proMeals = $proMealService->findAllByOwner($mmTestRestaurantUser, 1, array(
            'status' => ApiConstants::MEAL_STATUS_RUNNING,
        ));
        /** @var ProMeal $proMeal */
        $proMeal = $proMeals[0];
        $proMeal->addGuest($mmTestGuest);
        $proMeal->addGuest($mmTestGuest2);
        $manager->persist($proMeal);

        $proMealsFin = $proMealService->findAllByOwner($mmTestRestaurantUser, 1, array(
            'status' => ApiConstants::MEAL_STATUS_FINISHED,
        ));
        /** @var ProMeal $proMealFin */
        $proMealFin = $proMealsFin[0];
        $proMealFin->addGuest($mmTestGuest);
        $proMealFin->addGuest($mmTestGuest2);
        $manager->persist($proMealFin);

        $manager->flush();

        $mealOffer1 = $manager->getRepository('ApiBundle:Meal\MealOffer')->matching($criteria)->first();
        $mealTicketEntity = new BaseMealTicket();
        $mealTicketEntity->setCreatedBy($mmTestGuest);
        $mealTicketEntity->setHost($mmTestRestaurantUser);
        $mealTicketEntity->setGuest($mmTestGuest);
        $mealTicketEntity->setDescription('Mealticket description');
        $mealTicketEntity->setCurrency('EUR');
        $mealTicketEntity->setMmFee(0.25);
        $mealTicketEntity->setBaseMeal($proMeal);
        $mealTicketEntity->setPrice(10.00);
        $mealTicketEntity->setNumber('LoadMealTicket-TEST-#1');
        $mealTicketEntity->setNumberOfTickets(1);
        $mealTicketEntity->setSelectedMealOffer($mealOffer1);
        $mealTicketEntity->setTitel('Mealticket title');

        $mealTicketEntity->setPaymentType('SOFORT');
        $mealTicketEntity->setStatus(ApiConstants::MEAL_TICKET_STATUS_PAYED);

        $manager->persist($mealTicketEntity);

        $mealTicketEntity2 = new BaseMealTicket();
        $mealTicketEntity2->setHost($mmTestRestaurantUser);
        $mealTicketEntity2->setGuest($mmTestGuest);
        $mealTicketEntity2->setDescription('Mealticket description');
        $mealTicketEntity2->setCurrency('EUR');
        $mealTicketEntity2->setMmFee(0.25);
        $mealTicketEntity2->setBaseMeal($proMeal);
        $mealTicketEntity2->setPrice(5.55);
        $mealTicketEntity2->setNumber('2');
        $mealTicketEntity2->setNumberOfTickets(1);
        $mealTicketEntity2->setSelectedMealOffer($mealOffer1);

        $mealTicketEntity2->setTitel('Mealticket title');
        $mealTicketEntity2->setCreatedBy($mmTestGuest);
        $mealTicketEntity2->setPaymentType('SOFORT');
        $mealTicketEntity2->setStatus(ApiConstants::MEAL_TICKET_STATUS_PROCESSING);

        $manager->persist($mealTicketEntity2);

        $mealTicketEntity3 = new BaseMealTicket();
        $mealTicketEntity3->setHost($mmTestRestaurantUser);
        $mealTicketEntity3->setGuest($mmTestGuest);
        $mealTicketEntity3->setDescription('Mealticket description');
        $mealTicketEntity3->setCurrency('EUR');
        $mealTicketEntity3->setMmFee(0.25);
        $mealTicketEntity3->setBaseMeal($proMeal);
        $mealTicketEntity3->setPrice(100);
        $mealTicketEntity3->setNumber('3');
        $mealTicketEntity3->setNumberOfTickets(1);
        $mealTicketEntity3->setSelectedMealOffer($mealOffer1);

        $mealTicketEntity3->setTitel('Mealticket title');
        $mealTicketEntity3->setCreatedBy($mmTestGuest);
        $mealTicketEntity3->setPaymentType('SOFORT');
        $mealTicketEntity3->setStatus(ApiConstants::MEAL_TICKET_STATUS_ERROR);

        $manager->persist($mealTicketEntity3);

        $mealTicketEntity4 = new BaseMealTicket();
        $mealTicketEntity4->setHost($mmTestRestaurantUser);
        $mealTicketEntity4->setGuest($mmTestGuest);
        $mealTicketEntity4->setDescription('Mealticket description');
        $mealTicketEntity4->setCurrency('EUR');
        $mealTicketEntity4->setMmFee(0.25);
        $mealTicketEntity4->setBaseMeal($proMeal);
        $mealTicketEntity4->setPrice(5.55);
        $mealTicketEntity4->setNumber('4');
        $mealTicketEntity4->setNumberOfTickets(1);
        $mealTicketEntity4->setSelectedMealOffer($mealOffer1);

        $mealTicketEntity4->setTitel('Mealticket title');
        $mealTicketEntity4->setCreatedBy($mmTestGuest);
        $mealTicketEntity4->setPaymentType('SOFORT');
        $mealTicketEntity4->setStatus(ApiConstants::MEAL_TICKET_STATUS_CREATED);
        // maybe we can simulate and test the payment call after all ...
        $mealTicketEntity2->setHash('TESTABLE');

        $manager->persist($mealTicketEntity4);

        $mealTicketEntity5 = new BaseMealTicket();
        $mealTicketEntity5->setHost($mmTestRestaurantUser);
        $mealTicketEntity5->setGuest($mmTestGuest);
        $mealTicketEntity5->setDescription('Mealticket description');
        $mealTicketEntity5->setCurrency('EUR');
        $mealTicketEntity5->setMmFee(0.25);
        $mealTicketEntity5->setBaseMeal($proMeal);
        $mealTicketEntity5->setPrice(5.55);
        $mealTicketEntity5->setNumber('5');
        $mealTicketEntity5->setNumberOfTickets(1);
        $mealTicketEntity5->setSelectedMealOffer($mealOffer1);

        $mealTicketEntity5->setTitel('Mealticket title');
        $mealTicketEntity5->setCreatedBy($mmTestGuest);
        $mealTicketEntity5->setPaymentType('SOFORT');
        $mealTicketEntity5->setStatus(ApiConstants::MEAL_TICKET_STATUS_CANCELLED);

        $manager->persist($mealTicketEntity5);

        // MealTicket Status: USED;
        $mealTicketEntity6 = new BaseMealTicket();
        $mealTicketEntity6->setHost($mmTestRestaurantUser);
        $mealTicketEntity6->setGuest($mmTestGuest);
        $mealTicketEntity6->setDescription('Mealticket description');
        $mealTicketEntity6->setCurrency('EUR');
        $mealTicketEntity6->setMmFee(0.25);
        $mealTicketEntity6->setBaseMeal($proMeal);
        $mealTicketEntity6->setPrice(5.55);
        $mealTicketEntity6->setNumber('6');
        $mealTicketEntity6->setNumberOfTickets(1);
        $mealTicketEntity6->setSelectedMealOffer($mealOffer1);

        $mealTicketEntity6->setTitel('Mealticket title');
        $mealTicketEntity6->setCreatedBy($mmTestGuest);
        $mealTicketEntity6->setPaymentType('SOFORT');
        $mealTicketEntity6->setStatus(ApiConstants::MEAL_TICKET_STATUS_USED);

        $manager->persist($mealTicketEntity6);

        // MealTicket Status: PAYED, FINISHED;
        $mealTicketEntity7 = new BaseMealTicket();
        $mealTicketEntity7->setHost($mmTestRestaurantUser);
        $mealTicketEntity7->setGuest($mmTestGuest);
        $mealTicketEntity7->setDescription('Mealticket description');
        $mealTicketEntity7->setCurrency('EUR');
        $mealTicketEntity7->setMmFee(0.25);
        $mealTicketEntity7->setBaseMeal($proMealFin);
        $mealTicketEntity7->setPrice(10.00);
        $mealTicketEntity7->setNumber('7');
        $mealTicketEntity7->setNumberOfTickets(1);
        $mealTicketEntity7->setSelectedMealOffer($mealOffer1);

        $mealTicketEntity7->setTitel('Mealticket title');
        $mealTicketEntity7->setCreatedBy($mmTestGuest);
        $mealTicketEntity7->setPaymentType('SOFORT');
        $mealTicketEntity7->setStatus(ApiConstants::MEAL_TICKET_STATUS_PAYED);

        $manager->persist($mealTicketEntity7);

        $manager->flush();
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 45;
    }
}
