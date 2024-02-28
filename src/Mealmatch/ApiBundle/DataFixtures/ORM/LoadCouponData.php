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
use Mealmatch\ApiBundle\Entity\Coupon\Coupon;
use Mealmatch\ApiBundle\MealMatch\UserManager;
use MMUserBundle\Entity\MMUser;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @todo: Finish PHPDoc!
 * Create 2 MealTickets with status PAYED to show in RestaurantView.
 */
class LoadCouponData implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
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

        $testCouponCodeOK = new Coupon();
        $testCouponCodeOK->setCode('#OK');
        $testCouponCodeOK->setTitle('#OK-Coupon-Test');
        $testCouponCodeOK->setDescription('#OK-Coupon-Test');
        $testCouponCodeOK->setValue(5.00);
        $testCouponCodeOK->setLanguage('de');
        $testCouponCodeOK->setCurrency('EUR');
        $testCouponCodeOK->setAvailableAmount(9999);
        // this makes it active... e.g. redeemable, claimable
        $testCouponCodeOK->setStatus('active');

        $testCouponCodeInActive = new Coupon();
        $testCouponCodeInActive->setCode('#NEW');
        $testCouponCodeInActive->setValue(5.00);
        $testCouponCodeInActive->setLanguage('de');
        $testCouponCodeInActive->setCurrency('EUR');
        $testCouponCodeInActive->setAvailableAmount(1);

        $testCouponCodeNonAvailable = new Coupon();
        $testCouponCodeNonAvailable->setCode('#UNAVAIL');
        $testCouponCodeNonAvailable->setValue(5.00);
        $testCouponCodeNonAvailable->setLanguage('de');
        $testCouponCodeNonAvailable->setCurrency('EUR');
        // this makes it active, but "used up" all available amount (0)
        $testCouponCodeNonAvailable->setAvailableAmount(0);
        $testCouponCodeNonAvailable->setStatus('active');

        $manager->persist($testCouponCodeOK);
        $manager->persist($testCouponCodeInActive);
        $manager->persist($testCouponCodeNonAvailable);
        $manager->flush();
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 55;
    }
}
