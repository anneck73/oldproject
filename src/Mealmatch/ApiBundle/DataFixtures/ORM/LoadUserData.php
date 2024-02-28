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
use Mealmatch\ApiBundle\Entity\Restaurant\RestaurantAddress;
use Mealmatch\ApiBundle\MealMatch\UserManager;
use Mealmatch\ApiBundle\Model\GeoAddressServiceData;
use Mealmatch\ApiBundle\Services\GeoAddressService;
use MMUserBundle\Entity\MMRestaurantProfile;
use MMUserBundle\Entity\MMUser;
use MMUserBundle\Entity\MMUserPaymentProfile;
use MMUserBundle\Entity\MMUserProfile;
use MMUserBundle\Entity\RestaurantFile;
use MMUserBundle\Entity\RestaurantImage;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This Fixture loads default users into the db.
 *
 * MMTestUser, MMTestHost, MMTestRestaurant, MMUserPaymentProfile, MMUserProfile
 */
class LoadUserData implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{
    private $container;

    /**
     * Sets the symfony container.
     *
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Loading the data contained in the fixture.
     *
     * @param ObjectManager $manager the ORM to use
     */
    public function load(ObjectManager $manager)
    {
        /** @var UserManager $userManager */
        $userManager = $this->container->get('api.user_manager');

        /** @var MMUser $testGuestUser */
        $testGuestUser = $userManager->createUser();
        $testGuestUser->setUsername('MMTestGuest');
        $testGuestUser->getProfile()->setFirstName('MMGuest');
        $testGuestUser->getProfile()->setLastName('TESTUSER');
        $testGuestUser->setPlainPassword('MMTest');
        $testGuestUser->setEmail('mmtest.payer2@mealmatch.de');
        $testGuestUser->getProfile()->setPayPalEmail('mmtest.payer2@mealmatch.de');
        $testGuestUser->setEnabled(true);
        $testGuestUser->addRole('ROLE_HOME_USER');

        // MangopayID's from sandbox system for testing the ui quickly
        // The GUEST user get a valid ID, walletID and BankAccountID.
        $testGuestUser->getPaymentProfile()->setMangopayID('64272499');
        $testGuestUser->getPaymentProfile()->setMangopayWalletID('64272500');
        $testGuestUser->getPaymentProfile()->setMangopayBankAccountId(64392562);
        $manager->persist($testGuestUser);

        /** @var MMUser $testGuestUser2 */
        $testGuestUser2 = $userManager->createUser();
        $testGuestUser2->setUsername('MMTestGuest2');
        $testGuestUser2->getProfile()->setFirstName('MMGuest2');
        $testGuestUser2->getProfile()->setLastName('TESTUSER2');
        $testGuestUser2->setPlainPassword('MMTest');
        $testGuestUser2->setEmail('mmtest.payer3@mealmatch.de');
        $testGuestUser2->getProfile()->setPayPalEmail('mmtest.payer2@mealmatch.de');
        $testGuestUser2->setEnabled(true);
        $testGuestUser2->addRole('ROLE_USER');
        $manager->persist($testGuestUser2);

        /**
         * Test User for Home + Hosting from Home via Mangopay.
         * https://dashboard.sandbox.mangopay.com/User/67376907/Details
         * MangopayID: 67376907
         * MangopayWalletID:.67376908
         * MangopayBankaccountID:.67380740.
         */

        /** @var MMUser $testHostUser */
        $testHostUser = $userManager->createUser();
        $testHostUser->setUsername('MMTestHost');
        $testHostUser->setPlainPassword('MMTest');
        $testHostUser->getProfile()->setFirstName('MMHost');
        $testHostUser->getProfile()->setLastName('TESTUSER');
        $testHostUser->setEmail('mmtest.host@mealmatch.de');
        $testHostUser->getProfile()->setPayPalEmail('mmtest.host@mealmatch.de');
        $testHostUser->setEnabled(true);

        $testHostUser->addRole('ROLE_USER');
        $testHostUser->addRole('ROLE_HOME_USER');

        // MangopayID's from sandbox system for testing the ui quickly
        // The Host (HomeMeal) user get a valid ID, walletID and BankAccountID.
        $testHostUser->getPaymentProfile()->setMangopayID(67376907);
        $testHostUser->getPaymentProfile()->setMangopayWalletID(67376908);
        $testHostUser->getPaymentProfile()->setMangopayBankAccountId(67380740);
        $testHostUser->addRole('ROLE_HOME_HOST_USER');

        $manager->persist($testHostUser);

        /** @var MMUser $testRestaurantUser */
        $testRestaurantUser = $userManager->createRestaurantUser();
        $testRestaurantUser->setUsername('MMTestRestaurant');
        $testRestaurantUser->setPlainPassword('MMTest');
        $testRestaurantUser->getProfile()->setFirstName('MMRestaurant');
        $testRestaurantUser->getProfile()->setLastName('TESTUSER');
        $testRestaurantUser->setEmail('mmtest.company@mealmatch.de');
        $testRestaurantUser->getProfile()->setPayPalEmail('mmtest.company@mealmatch.de');
        $testRestaurantUser->setEnabled(true);
        $testRestaurantUser->addRole('ROLE_RESTAURANT_USER');

        // RestaurantProfile ....

        /** @var MMRestaurantProfile $testRestaurantProfile */
        $testRestaurantProfile = $testRestaurantUser->getRestaurantProfile();
        $testRestaurantProfile->setPayPalEmail('mmtest.company@mealmatch.de');
        $testRestaurantProfile->setDescription('LoadUserData-Testbeschreibung');

        // GeoAddress for RestaurantAddress

        /** @var GeoAddressService $geoAddressService */
        $geoAddressService = $this->container->get('api.geo_address.service');
        $testRestaurantAddress = new RestaurantAddress();
        $testRestaurantAddress->setLocationString('Am Parkfriedhof 28, Essen');
        /** @var GeoAddressServiceData $serviceData */
        $serviceData = $geoAddressService->updateGeoAddress($testRestaurantAddress);
        $geoRestaurantAddress = $serviceData->getEntity('GeoAddress');
        $testRestaurantProfile->addAddress($geoRestaurantAddress);

        $testRestaurantProfile->setAuthorizedRepresentative('LoadUserData-Vertretungsberechtigte Person');
        $testRestaurantProfile->setBankIBAN('FR7611808009101234567890147');
        $testRestaurantProfile->setCommercialRegisterNumber('LoadUserData-###');
        $testRestaurantProfile->setCompany('LoadUserData-Company-Name');
        $testRestaurantProfile->setContactAddress('LoadUserData-Kontaktadresse');
        $testRestaurantProfile->setContactEmail('LoadUserData@kontakt.email');
        $testRestaurantProfile->setContactPhone('123456789');
        $testRestaurantProfile->setName('LoadUserData-Restaurant-Name');
        $testRestaurantProfile->setType('LoadUserData-Restaurant-Type');
        $testRestaurantProfile->setTaxID('LoadUserData-TAX-ID');
        $testRestaurantFile = new RestaurantFile();
        $testRestaurantFile->setFileName('LoadUserData-TestFileName');
        $manager->persist($testRestaurantFile);
        $testRestaurantProfile->addLegalFile($testRestaurantFile);

        for ($i = 0; $i <= 5; ++$i) {
            $testRestaurantImage = new RestaurantImage();
            $testRestaurantImage->setFileName('Image1-TEST-'.$i);
            $manager->persist($testRestaurantImage);
            $testRestaurantProfile->addPicture($testRestaurantImage);
        }
        $manager->persist($testRestaurantUser);

        /** @var MMUser SYSTEM */
        $system = $userManager->createUser();
        $system->setUsername('SYSTEM');
        $system->setPlainPassword('123');
        $system->getProfile()->setFirstName('SYSTEM');
        $system->getProfile()->setLastName('MEALMATCH');
        $system->setEmail('mailer@mealmatch.de');
        $system->setEnabled(true);
        $system->addRole('ROLE_SYSTEM_USER');
        $system->addRole('ROLE_ADMIN');

        /** @var MMUserProfile userProfile1 */
        $userProfile1 = $testGuestUser->getProfile();
        $userProfile1->setCountry('de');
        $userProfile1->setBirthday(date_create());

        $manager->persist($system);
        $manager->persist($userProfile1);
        $manager->flush();
    }

    /**
     * Returns the order value of this fixture.
     *
     * @return int order of execution value
     */
    public function getOrder()
    {
        return 1;
    }
}
