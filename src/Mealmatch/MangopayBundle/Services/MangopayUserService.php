<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\MangopayBundle\Services;

use Doctrine\ORM\EntityManager;
use MangoPay\Address;
use MangoPay\Address as MangoAddress;
use MangoPay\Libraries\Exception;
use MangoPay\Libraries\ResponseException;
use MangoPay\MangoPayApi;
use MangoPay\User;
use MangoPay\UserLegal;
use MangoPay\UserNatural;
use Mealmatch\MangopayBundle\Exceptions\MangopayApiException;
use MMUserBundle\Entity\MMUser;
use MMUserBundle\Entity\MMUserPaymentProfile;
use Psr\Log\LoggerInterface as Logger;
use Symfony\Component\Translation\Translator;

/**
 * @todo: finish this service class, then use it where old methods can be replaced!
 * Class MangopayUserService
 */
class MangopayUserService extends BaseMangopayService
{
    /** @var MangoPayApi $mangopayApi */
    private $mangopayApi;
    /** @var MangopayApiService $mangopayServiceApi */
    private $mangopayServiceApi;

    public function __construct(
        Logger $logger,
        EntityManager $entityManager,
        Translator $translator,
        MangopayApiService $mangopayApiService
        ) {
        parent::__construct($logger, $entityManager, $translator);
        $this->mangopayApi = $mangopayApiService->getMangopayApi();
        $this->mangopayApiService = $mangopayApiService;
    }

    /**
     * Example array data received by this method:.
     *
     *  {
     *      "FirstName":"HomeHost",
     *      "LastName":"HomeHost",
     *      "AddressLine1":"HomeHost",
     *      "AddressLine2":"HomeHost",
     *      "City":"Essen",
     *      "Region":"NRW",
     *      "PostalCode":"48315",
     *      "Country":"DE",
     *      "Birthday":
     *          {
     *              "day":"7",
     *              "month":"5",
     *              "year":"1973"
     *          },
     *      "CountryOfResidence":"DE",
     *      "Nationality":"DE",
     * }
     *
     * @param array $userNaturalData
     *
     * @throws \Exception
     *
     * @return UserNatural
     */
    public function createUserNaturalFromArray(array $userNaturalData): UserNatural
    {
        /** @var UserNatural $userNatural */
        $userNatural = new UserNatural();
        $userNatural->Tag = 'Mealmatch HomeHostUser';
        $userNatural->FirstName = $userNaturalData['FirstName'];
        $userNatural->LastName = $userNaturalData['LastName'];
        $userNatural->CountryOfResidence = $userNaturalData['CountryOfResidence'];
        $userNatural->Nationality = $userNaturalData['Nationality'];

        // Payment-Address object (mangopay)
        $userNaturalAddress = new Address();
        $userNaturalAddress->AddressLine1 = $userNaturalData['AddressLine1'];
        $userNaturalAddress->AddressLine2 = $userNaturalData['AddressLine2'];
        $userNaturalAddress->City = $userNaturalData['City'];
        $userNaturalAddress->Country = $userNaturalData['CountryOfResidence'];
        $userNaturalAddress->PostalCode = $userNaturalData['PostalCode'];
        $userNaturalAddress->Region = $userNaturalData['Region'];
        $userNatural->Address = $userNaturalAddress;

        // Email for payment
        $userNatural->Email = $userNaturalData['Email'];

        // Birthday to timestamp
        $day = $userNaturalData['Birthday']['day'];
        $month = $userNaturalData['Birthday']['month'];
        $year = $userNaturalData['Birthday']['year'];
        $birthday = new \DateTime("$day.$month.$year");
        $birthdayTimestamp = $birthday->getTimestamp();
        $userNatural->Birthday = $birthdayTimestamp;

        // Create new mangopay user for host
        $this->logger->debug('mangopayApi->Users->Create'.json_encode($userNatural));

        return $userNatural;
    }

    public function createUserLegalFromArray(array $userLegalData): UserLegal
    {
        $userLegal = new UserLegal();
        $userLegal->Tag = 'Mealmatch LegalUser';
        $userLegal->HeadquartersAddress = new MangoAddress();
        $userLegal->HeadquartersAddress->AddressLine1 = $userLegalData['HQAddressLine1'];
        $userLegal->HeadquartersAddress->AddressLine2 = $userLegalData['HQAddressLine2'];
        $userLegal->HeadquartersAddress->City = $userLegalData['HQCity'];
        $userLegal->HeadquartersAddress->Region = $userLegalData['HQRegion'];
        $userLegal->HeadquartersAddress->PostalCode = $userLegalData['HQPostalCode'];
        $userLegal->HeadquartersAddress->Country = $userLegalData['HQCountry'];
        $userLegal->LegalPersonType = 'BUSINESS';
        $userLegal->Name = $userLegalData['Name'];
        $userLegal->Email = $userLegalData['Email'];
        $userLegal->CompanyNumber = $userLegalData['CompanyNumber'];
        $userLegal->LegalRepresentativeAddress = new MangoAddress();
        $userLegal->LegalRepresentativeAddress->AddressLine1 = $userLegalData['LRAddressLine1'];
        $userLegal->LegalRepresentativeAddress->AddressLine2 = $userLegalData['LRAddressLine2'];
        $userLegal->LegalRepresentativeAddress->City = $userLegalData['LRAddressCity'];
        $userLegal->LegalRepresentativeAddress->Region = $userLegalData['LRAddressRegion'];
        $userLegal->LegalRepresentativeAddress->PostalCode = $userLegalData['LRAddressPostalCode'];
        $userLegal->LegalRepresentativeAddress->Country = $userLegalData['LRAddressCountry'];

        $day = $userLegalData['LRBirthday']['day'];
        $month = $userLegalData['LRBirthday']['month'];
        $year = $userLegalData['LRBirthday']['year'];
        $birthday = new \DateTime("$day.$month.$year");
        $birthdayTimestamp = $birthday->getTimestamp();
        $userLegal->LegalRepresentativeBirthday = $birthdayTimestamp;
        $userLegal->LegalRepresentativeCountryOfResidence = $userLegalData['LRCountryOfResidence'];
        $userLegal->LegalRepresentativeNationality = $userLegalData['LRNationality'];
        $userLegal->LegalRepresentativeEmail = $userLegalData['LREmail'];
        $userLegal->LegalRepresentativeFirstName = $userLegalData['LRFirstName'];
        $userLegal->LegalRepresentativeLastName = $userLegalData['LRLastName'];

        // Create new mangopay user for host
        $this->logger->debug('mangopayApi->Users->Create'.json_encode($userLegal));

        return $userLegal;
    }

    public function createUserLegalFrom(MMUser $user): UserLegal
    {
        $userLegal = new UserLegal();
        $userLegal->Tag = 'Mealmatch LegalUser';
        $userLegal->HeadquartersAddress = new MangoAddress();
        $userLegal->HeadquartersAddress->AddressLine1 = $user->getRestaurantProfile()->getLegalRepresentativeAddressLine1();
        $userLegal->HeadquartersAddress->AddressLine2 = $user->getRestaurantProfile()->getLegalRepresentativeAddressLine2();
        $userLegal->HeadquartersAddress->City = $user->getRestaurantProfile()->getLegalRepresentativeCity();
        $userLegal->HeadquartersAddress->Region = $user->getRestaurantProfile()->getLegalRepresentativeRegion();
        $userLegal->HeadquartersAddress->PostalCode = $user->getRestaurantProfile()->getLegalRepresentativePostalCode();
        $userLegal->HeadquartersAddress->Country = $user->getRestaurantProfile()->getCountry();
        $userLegal->LegalPersonType = 'BUSINESS';
        $userLegal->Name = $user->getRestaurantProfile()->getName();
        $userLegal->Email = $user->getRestaurantProfile()->getContactEmail();
        $userLegal->CompanyNumber = $user->getRestaurantProfile()->getTaxID();

        $userLegal->LegalRepresentativeAddress = new MangoAddress();
        $userLegal->LegalRepresentativeAddress->AddressLine1 = $user->getRestaurantProfile()->getLegalRepresentativeAddressLine1();
        $userLegal->LegalRepresentativeAddress->AddressLine2 = $user->getRestaurantProfile()->getLegalRepresentativeAddressLine2();
        $userLegal->LegalRepresentativeAddress->City = $user->getRestaurantProfile()->getLegalRepresentativeCity();
        $userLegal->LegalRepresentativeAddress->Region = $user->getRestaurantProfile()->getLegalRepresentativeRegion();
        $userLegal->LegalRepresentativeAddress->PostalCode = $user->getRestaurantProfile()->getLegalRepresentativePostalCode();
        $userLegal->LegalRepresentativeAddress->Country = $user->getRestaurantProfile()->getCountry();
        $userLegal->LegalRepresentativeBirthday = $user->getRestaurantProfile()->getBirthday()->getTimestamp();
        $userLegal->LegalRepresentativeCountryOfResidence = $user->getRestaurantProfile()->getCountry();
        $userLegal->LegalRepresentativeNationality = $user->getRestaurantProfile()->getNationality();
        $userLegal->LegalRepresentativeEmail = $user->getRestaurantProfile()->getContactEmail();
        $userLegal->LegalRepresentativeFirstName = $user->getRestaurantProfile()->getLegalRepresentativeFirstName();
        $userLegal->LegalRepresentativeLastName = $user->getRestaurantProfile()->getLegalRepresentativeLastName();

        // Create new mangopay user for host
        $this->logger->debug('mangopayApi->Users->Create'.json_encode($userLegal));

        return $userLegal;
    }

    /**
     * Calls mangopayApi->Users->Create($userLegal) and returns the created mangopay user ID or throwns a
     * MangopayApiException if the remote call fails.
     *
     * @param UserLegal $userLegal
     *
     * @throws MangopayApiException
     *
     * @return int
     */
    public function doCreateUserLegal(UserLegal $userLegal): int
    {
        try {
            $createdUser = $this->mangopayApi->Users->Create($userLegal);
        } catch (Exception $mangopayException) {
            $errMsg = 'Failed to doCreateUserLegal: '.$mangopayException->getMessage();
            $this->logger->error($errMsg);
            throw new MangopayApiException($errMsg);
        }

        return $createdUser->Id;
    }

    public function createUserNaturalFrom(MMUser $user): UserNatural
    {
        $userNatural = new UserNatural();

        /** @var MMUserPaymentProfile $paymentProfile */
        $paymentProfile = $user->getPaymentProfile();

        // If this user already has a mangopayID, we return that one.
        if ($paymentProfile->hasMangopayID()) {
            $mangopayID = $paymentProfile->getMangopayID();
            $this->logger->debug('Returned already existing MangopayUserNatural('.$mangopayID.')');

            return $this->mangopayApi->Users->Get($mangopayID);
        }

        $userNatural->Email = $user->getEmail();
        $userNatural->FirstName = $user->getProfile()->getFirstName();
        $userNatural->LastName = $user->getProfile()->getLastName();
        $userNatural->PersonType = 'NATURAL';
        $userNatural->Birthday = $user->getProfile()->getBirthday()->getTimestamp();
        $userNatural->Nationality = $user->getProfile()->getCountry();
        $userNatural->CountryOfResidence = $user->getProfile()->getCountry();

        // WEBAPP-349: Workaround .. but we should actually not use the user-profile for this anymore.
        $userNatural->Address = new Address();
        $userNatural->Address->AddressLine1 = $user->getProfile()->getAddressLine1();
        $userNatural->Address->PostalCode = $user->getProfile()->getAreaCode();
        $userNatural->Address->City = $user->getProfile()->getCity();
        $userNatural->Address->Region = $user->getProfile()->getState();
        $userNatural->Address->Country = $user->getProfile()->getCountry();

        return $userNatural;
    }

    /**
     * Calls mangopayApi->Users->Create($userNatural) and returns the created mangopay user ID or throwns a
     * MangopayApiException if the remote call fails.
     *
     * @param UserNatural $userNatural
     *
     * @throws MangopayApiException
     *
     * @return int
     */
    public function doCreateUserNatural(UserNatural $userNatural): int
    {
        try {
            $createdUser = $this->mangopayApi->Users->Create($userNatural);
        } catch (Exception $mangopayException) {
            $errMsg = 'Failed to doCreateUserNatural using: '.json_encode($userNatural);
            $this->logger->error($errMsg);
            if ($mangopayException instanceof ResponseException) {
                $mPREXCP = $mangopayException;
                /* @var ResponseException $errorDetails */
                $errorDetails = $mPREXCP->GetErrorDetails()->Errors;
                $errMsg .= json_encode($errorDetails);
                $this->logger->error(json_encode($errorDetails));
            }

            throw new MangopayApiException($errMsg);
        }

        return $createdUser->Id;
    }

    /**
     * @param int $mangopayID
     *
     * @return User
     */
    public function getUser(int $mangopayID): User
    {
        return $this->mangopayApi->Users->Get($mangopayID);
    }
}
