<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use MMApiBundle\MealMatch\UserManager;
use MMUserBundle\Entity\MMUser;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadGuestsIntoMeals implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
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

        /** @var MMUser $mmTestGuest */
        $mmTestRestaurant = $userManager->findUserByUsername('MMTestRestaurant');

        /** @var EntityManager $entityManager */
        $entityManager = $this->container->get('doctrine.orm.entity_manager');

        $proMeal = $entityManager->getRepository('ApiBundle:Meal\ProMeal')->findOneBy(
            array(
                'leaf' => 1,
                'host' => $mmTestRestaurant,
            )
        );

        // $proMeal->addGuest($mmTestGuest);

        $homeMeal = $entityManager->getRepository('ApiBundle:Meal\HomeMeal')->findOneBy(
            array(
                'leaf' => 1,
                'host' => $mmTestHost,
            )
        );

        $homeMeal->addGuest($mmTestGuest);
        $entityManager->persist($homeMeal);
        // $entityManager->persist($proMeal);
        $entityManager->flush();
    }

    public function getOrder()
    {
        return 51;
    }
}
