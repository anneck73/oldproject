<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\MealMatch;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Doctrine\UserManager as FOSUserManager;
use FOS\UserBundle\Model\UserInterface;
use Mealmatch\ApiBundle\Entity\Restaurant\RestaurantAddress;
use Mealmatch\ApiBundle\Entity\User\Profiles\CouponProfile;
use Mealmatch\ApiBundle\Exceptions\UserNotFoundException;
use MMUserBundle\Entity\MMRestaurantProfile;
use MMUserBundle\Entity\MMUser;
use MMUserBundle\Entity\MMUserPaymentProfile;
use MMUserBundle\Entity\MMUserProfile;
use MMUserBundle\Entity\MMUserSettings;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

/**
 * The UserManager creates and updates Users.
 *
 * This UserManager extends FOSUserManager to integrate with FOS USer and HWIOAuth
 */
class UserManager extends FOSUserManager
{
    /**
     * Default ... creates a new entity MMUser with attached
     * MMUserProfile
     * MMUserSettings
     * MMRestauranProfile.
     * CouponProfile.
     *
     * Note: All of the users do get all of the profile types to make role transition easy.
     *
     * @return mixed
     */
    public function createUser()
    {
        /** @var MMUser $baseUser */
        $baseUser = parent::createUser();

        /** @var MMUserProfile $profile */
        $profile = new MMUserProfile();
        $baseUser->setProfile($profile);

        /** @var MMUserPaymentProfile $paymentProfile */
        $paymentProfile = new MMUserPaymentProfile();
        $baseUser->setPaymentProfile($paymentProfile);

        /** @var MMUserSettings $settings */
        $settings = new MMUserSettings();
        $baseUser->setSettings($settings);

        /** @var MMRestaurantProfile $restaurantProfile */
        $restaurantProfile = new MMRestaurantProfile();
        $restaurantAddress = new RestaurantAddress();
        $restaurantAddress->setCoordinates(50.93333, 6.95);
        $restaurantProfile->addAddress($restaurantAddress);
        $baseUser->setRestaurantProfile($restaurantProfile);

        /** @var CouponProfile $couponProfile */
        $couponProfile = new CouponProfile();
        $baseUser->setCoupontProfile($couponProfile);

        return $baseUser;
    }

    /**
     * Creates a new MMUser entity with the role ROLE_HOME_USER added.
     *
     * @return MMUser
     */
    public function createHomeUser(): MMUser
    {
        /** @var MMUser $baseUser */
        $baseUser = $this->createUser();
        $baseUser->addRole('ROLE_HOME_USER');

        return $this->getTypedUserInterface($baseUser);
    }

    /**
     * Creates a new MMUser entity with the role ROLE_RESTAURANT_USER added.
     *
     * @return MMUser
     */
    public function createRestaurantUser(): MMUser
    {
        /** @var MMUser $baseUser */
        $baseUser = $this->createUser();
        $baseUser->addRole('ROLE_RESTAURANT_USER');

        return $this->getTypedUserInterface($baseUser);
    }

    /**
     * Returns an existing user.
     *
     * The entity manager is used to query the database and return a MMUser entity.
     *
     * @param string $userNameOrEmail the user is specified using its username or the email address
     *
     * @throws \Mealmatch\ApiBundle\Exceptions\UserNotFoundException
     *
     * @return MMUser
     */
    public function getMealmatchUser(string $userNameOrEmail): MMUser
    {
        $userInterface = parent::findUserByUsernameOrEmail($userNameOrEmail);

        if (null !== $userInterface) {
            return $this->getTypedUserInterface($userInterface);
        }

        throw new UserNotFoundException();
    }

    public function findUnfinishedRegistrations(): Collection
    {
        $allUsers = new ArrayCollection(parent::findUsers());

        // return new ArrayCollection($allUsers);
        return $allUsers->filter(
          function (MMUser $user) {
              if ($user->isEnabled()) {
                  return false; // Already enabled ...
              }

              return true;
          }
        );
    }

    public function setImageUploader()
    {
        // @todo: rethink this method and remove if not used.
    }

    /**
     * Private helper to return the type expected.
     *
     * @param UserInterface $baseUser
     *
     * @throws UnsupportedUserException if the user is not an instance of MMUser
     *
     * @return MMUser
     */
    private function getTypedUserInterface(UserInterface $baseUser): MMUser
    {
        if ($baseUser instanceof MMUser) {
            return $baseUser;
        }

        throw new UnsupportedUserException('FATAL! User not of type MMuser!');
    }
}
