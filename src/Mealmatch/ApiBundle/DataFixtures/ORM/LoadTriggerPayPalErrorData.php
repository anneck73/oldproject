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
use Mealmatch\ApiBundle\Entity\Restaurant;
use Mealmatch\ApiBundle\Entity\Restaurant\RestaurantAddress;
use Mealmatch\ApiBundle\MealMatch\UserManager;
use Mealmatch\ApiBundle\Model\GeoAddressServiceData;
use Mealmatch\ApiBundle\Services\GeoAddressService;
use MMUserBundle\Entity\MMRestaurantProfile;
use MMUserBundle\Entity\MMUser;
use MMUserBundle\Entity\RestaurantFile;
use MMUserBundle\Entity\RestaurantImage;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This fixture loads User and Mealdata to trigger a PayPal-Error.
 */
class LoadTriggerPayPalErrorData implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{
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

        /** @var MMUser $hostWithSamePaypalAddress */
        $hostWithSamePaypalAddress = $userManager->createUser();
        $hostWithSamePaypalAddress->setUsername('HostWithSamePaypalAddress');
        $hostWithSamePaypalAddress->getProfile()->setFirstName('HostSamePaypalAddressTrigger');
        $hostWithSamePaypalAddress->getProfile()->setLastName('TESTUSER');
        $hostWithSamePaypalAddress->setPlainPassword('123');
        $hostWithSamePaypalAddress->setEmail('host.with.same.paypal.address@mealmatch.de');
        $hostWithSamePaypalAddress->getProfile()->setPayPalEmail('same.paypal.address.for.all@mealmatch.de');
        $hostWithSamePaypalAddress->setEnabled(true);
        $hostWithSamePaypalAddress->addRole('ROLE_USER');
        $manager->persist($hostWithSamePaypalAddress);

        /** @var MMUser $guestWithSamePaypalAddress */
        $guestWithSamePaypalAddress = $userManager->createUser();
        $guestWithSamePaypalAddress->setUsername('GuestWithSamePaypalAddress');
        $guestWithSamePaypalAddress->getProfile()->setFirstName('GuestSamePaypalAddressTrigger');
        $guestWithSamePaypalAddress->getProfile()->setLastName('TESTUSER');
        $guestWithSamePaypalAddress->setPlainPassword('123');
        $guestWithSamePaypalAddress->setEmail('guest.with.same.paypal.address@mealmatch.de');
        $guestWithSamePaypalAddress->getProfile()->setPayPalEmail('same.paypal.address.for.all@mealmatch.de');
        $guestWithSamePaypalAddress->setEnabled(true);
        $guestWithSamePaypalAddress->addRole('ROLE_USER');
        $manager->persist($guestWithSamePaypalAddress);

        /** @var MMUser $restaurantWithSamePaypalAddress */
        $restaurantWithSamePaypalAddress = $userManager->createUser();
        $restaurantWithSamePaypalAddress->setUsername('RestaurantWithSamePaypalAddress');
        $restaurantWithSamePaypalAddress->getProfile()->setFirstName('RestaurantSamePaypalAddressTrigger');
        $restaurantWithSamePaypalAddress->getProfile()->setLastName('TESTUSER');
        $restaurantWithSamePaypalAddress->setPlainPassword('123');
        $restaurantWithSamePaypalAddress->setEmail('restaurant.with.same.paypal.address@mealmatch.de');
        $restaurantWithSamePaypalAddress->getProfile()->setPayPalEmail('same.paypal.address.for.all@mealmatch.de');
        $restaurantWithSamePaypalAddress->setEnabled(true);
        $restaurantWithSamePaypalAddress->addRole('ROLE_RESTAURANT_USER');
        $restaurantWithSamePaypalAddress->addRole('ROLE_ADMIN');

        // RestaurantProfile

        /**
         * @var MMRestaurantProfile
         */
        $restaurantWithSamePaypalAddressProfile = $restaurantWithSamePaypalAddress->getRestaurantProfile();
        $restaurantWithSamePaypalAddressProfile->setPayPalEmail('same.paypal.address.for.all@mealmatch.de');
        $restaurantWithSamePaypalAddressProfile->setDescription('LoadUserData-Testbeschreibung');

        // GeoAddress for Restaurant Address

        /** @var GeoAddressService $geoAddressService */
        $geoAddressService = $this->container->get('api.geo_address.service');
        $restaurantAddress = new RestaurantAddress();
        $restaurantAddress->setLocationString('Maternusstraße 29, Köln');

        /** @var GeoAddressServiceData $serviceData */
        $serviceData = $geoAddressService->updateGeoAddress($restaurantAddress);
        $geoRestaurantAddress = $serviceData->getEntity('GeoAddress');
        $restaurantWithSamePaypalAddressProfile->addAddress($geoRestaurantAddress);

        $restaurantWithSamePaypalAddressProfile->setAuthorizedRepresentative('LoadUserData-Vertretungsberechtigte Person');
        $restaurantWithSamePaypalAddressProfile->setBankIBAN('LoadUserData-IBAN');
        $restaurantWithSamePaypalAddressProfile->setCommercialRegisterNumber('LoadUserData-###');
        $restaurantWithSamePaypalAddressProfile->setCompany('LoadUserData-Company-Name');
        $restaurantWithSamePaypalAddressProfile->setContactAddress('LoadUserData-Kontaktaddresse');
        $restaurantWithSamePaypalAddressProfile->setContactEmail('LoadUserData@kontakt.email');
        $restaurantWithSamePaypalAddressProfile->setContactPhone('123456789');
        $restaurantWithSamePaypalAddressProfile->setName('LoadUserData-Restaurant-Name');
        $restaurantWithSamePaypalAddressProfile->setType('LoadUserData-Restaurant-Type');
        $restaurantWithSamePaypalAddressProfile->setTaxID('LoadUserData-TAX-ID');
        $restaurantFile = new RestaurantFile();
        $restaurantFile->setFileName('LoadUserData-TestFileName');
        $manager->persist($restaurantFile);
        $restaurantWithSamePaypalAddressProfile->addLegalFile($restaurantFile);

        // Add five pictures from restaurant
        for ($i = 0; $i <= 5; ++$i) {
            $restaurantImage = new RestaurantImage();
            $restaurantImage->setFileName('Image1-TEST-'.$i);
            $manager->persist($restaurantImage);
            $restaurantWithSamePaypalAddressProfile->addPicture($restaurantImage);
        }
        $manager->persist($restaurantWithSamePaypalAddress);

        // Add 0 EUR MealOffer

        $proMealEntity = new ProMeal();
        $offer1 = new MealOffer();
        $offer1->setName('0 EUR Angebot');
        $offer1->setDescription('0 EUR Angebot um PayPal-Fehler zu triggern.');
        $offer1->setAvailableAmount(1);
        $offer1->setPrice(10.00);
        $offer1->setCurrency('EUR');
        $proMealEntity->addMealOffer($offer1);

        $mealEvent = new MealEvent();
        $mealEvent->setStartDateTime(new DateTime('now'));
        $mealEvent->setEndDateTime(new DateTime('+6 hours'));
        $mealEvent->setReoccuring(true);
        $mealEvent->setRrule('FREQ=DAILY;COUNT=1');
        $proMealEntity->addMealEvent($mealEvent);

        $proMealEntity->setMaxNumberOfGuest(3);
        $proMealEntity->setTitle('PayPal Test ...');
        $proMealEntity->setDescription('ProMeal Lauftext ...');
        $proMealEntity->setTableTopic('Pro Meal Table Topic PayPal Test');
        $proMealEntity->setHost($restaurantWithSamePaypalAddress);
        $proMealEntity->setSharedCost(0.00);
        $proMealEntity->setStartDateTime(new DateTime('4.2.4242 16:20'));
        $proMealEntity->setSharedCostCurrency('EUR');
        $proMealEntity->setStatus(ApiConstants::MEAL_STATUS_RUNNING);

        $serviceData = $geoAddressService->createMealAddressByLocation('Maternusstraße 29, Köln');
        $mealAddress = $serviceData->getMealAddress();
        $proMealEntity->addMealAddress($mealAddress);
        $cat1 = $manager->getRepository('ApiBundle:Meal\BaseMealCategory')->findAll()[0];
        $proMealEntity->addCategory($cat1);
        $serviceData = $this->container->get('api.pro_meal.service')->createFromEntity($proMealEntity);
        $createdRootProMeal = $serviceData->getProMeal();
        $this->container->get('api.meal.service')->createAllProMealEvents($createdRootProMeal, ApiConstants::MEAL_STATUS_RUNNING);
        $manager->flush();
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 60;
    }
}
