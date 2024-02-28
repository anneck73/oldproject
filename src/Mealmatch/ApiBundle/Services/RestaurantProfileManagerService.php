<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Services;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use MMUserBundle\Entity\MMRestaurantProfile;
use MMUserBundle\Entity\MMUserPaymentProfile;
use MMUserBundle\Entity\MMUserSettings;
use Symfony\Bridge\Monolog\Logger;

class RestaurantProfileManagerService
{
    const UPDATE_RESTAURANT_PROFILE_FAILED = 'Update RestaurantProfile failed: ';
    /** @var EntityManager $entityManager */
    private $entityManager;

    /** @var Logger $logger */
    private $logger;

    public function __construct(EntityManager $entityManager, Logger $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * @param \MMUserBundle\Entity\MMUser $user
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function autofixUserProfiles(\MMUserBundle\Entity\MMUser $user)
    {
        if (null === $user->getSettings()) {
            $userSettings = new MMUserSettings();
            $user->setSettings($userSettings);
        }
        if (null === $user->getPaymentProfile()) {
            $userPaymentProfile = new MMUserPaymentProfile();
            $user->setRestaurantProfile($userPaymentProfile);
        }
        if (null === $user->getRestaurantProfile()) {
            $restaurantProfile = new MMRestaurantProfile();
            $user->setRestaurantProfile($restaurantProfile);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * - Write all changes into DB.
     *
     * @param MMRestaurantProfile $restaurantProfile
     *
     * @return MMRestaurantProfile
     */
    public function updateRestaurantProfile(MMRestaurantProfile $restaurantProfile): MMRestaurantProfile
    {
        // Persist changes to profile ...
        try {
            $this->entityManager->persist($restaurantProfile);
        } catch (ORMException $ormExceptionPersist) {
            $this->logger->error(self::UPDATE_RESTAURANT_PROFILE_FAILED.$ormExceptionPersist->getMessage());
        }
        // Write changes to DB
        try {
            $this->entityManager->flush();
        } catch (OptimisticLockException $opLockException) {
            $this->logger->error(self::UPDATE_RESTAURANT_PROFILE_FAILED.$ormExceptionPersist->getMessage());
        } catch (ORMException $ormExceptionFlush) {
            $this->logger->error(self::UPDATE_RESTAURANT_PROFILE_FAILED.$ormExceptionPersist->getMessage());
        }
        // Return updated or unchanged profile
        return $restaurantProfile;
    }
}
