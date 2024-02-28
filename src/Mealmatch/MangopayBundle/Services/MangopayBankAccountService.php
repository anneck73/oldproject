<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\MangopayBundle\Services;

use Doctrine\ORM\EntityManager;
use MangoPay\Address;
use MangoPay\BankAccount;
use MangoPay\BankAccountDetailsIBAN;
use MangoPay\MangoPayApi;
use MangoPay\User as MangopayUser;
use Mealmatch\ApiBundle\ApiConstants;
use MMUserBundle\Entity\MMUser;
use MMUserBundle\Entity\MMUserPaymentProfile;
use Psr\Log\LoggerInterface as Logger;
use Symfony\Component\Translation\Translator;

/**
 * The MangopayBankAccountService class handles all BankAccount interactions with Mealmatch.
 *
 * It creates local BankAccount object from Mealmatch users.
 * It creates remote BankAccount objects for specific Mangopay users.
 */
class MangopayBankAccountService extends BaseMangopayService
{
    /** @var MangoPayApi $mangopayApi */
    private $mangopayApi;

    public function __construct(
        Logger $logger,
        EntityManager $entityManager,
        Translator $translator,
        MangopayApiService $mangopayApiService)
    {
        parent::__construct($logger, $entityManager, $translator);
        $this->mangopayApi = $mangopayApiService->getMangopayApi();
    }

    /**
     * Create a local BankAccount for a Mealmatch user, depending on it's role.
     *
     * This method creates a BankAccount using the users
     * - RestaurantProfile if the users role is ROLE_RESTAURANT_USER
     * - Profile, Paymentprofile if the users role is ROLE_HOME_USER.
     *
     * @todo: fill region with a valid/sane value.
     *
     * @param MMUser $user
     *
     * @return BankAccount|null
     */
    public function createBankAccountUser(MMUser $user): ?BankAccount
    {
        $newBankAccount = new \MangoPay\BankAccount();

        $newBankAccount->Type = 'IBAN';
        $newBankAccount->Details = new BankAccountDetailsIBAN();

        if ($user->hasRole('ROLE_RESTAURANT_USER')) {
            $hostRestaurantProfile = $user->getRestaurantProfile();
            $newBankAccount->Details->IBAN = $hostRestaurantProfile->getBankIBAN();
            $newBankAccount->Details->BIC = $hostRestaurantProfile->getBankBIC();
            $newBankAccount->OwnerName = $hostRestaurantProfile->getCompany();
            $ownerAddress = new Address();
            $hostRestaurantAddress = $hostRestaurantProfile->getAddress();
            $ownerAddress->Country = $hostRestaurantAddress->getCountryCode();
            $ownerAddress->PostalCode = $hostRestaurantAddress->getPostalCode();
            $ownerAddress->Region = $hostRestaurantAddress->getState();
            $ownerAddress->City = $hostRestaurantAddress->getCity();
            $ownerAddress->AddressLine1 =
                $hostRestaurantAddress->getStreetName().''.$hostRestaurantAddress->getStreetNumber();
            $ownerAddress->AddressLine2 = $hostRestaurantAddress->getExtraLine1();
            $newBankAccount->OwnerAddress = $ownerAddress;
        }

        if ($user->hasRole('ROLE_HOME_USER')) {
            /** @var MMUserPaymentProfile $paymentProfile */
            $paymentProfile = $user->getPaymentProfile();
            $newBankAccount->Details->IBAN = $paymentProfile->getIban();
            $newBankAccount->Details->BIC = $paymentProfile->getBic();
            $newBankAccount->OwnerName = $user->getProfile()->getLastName().', '.$user->getProfile()->getFirstName();
            $ownerAddress = new Address();
            $userProfile = $user->getProfile();
            $ownerAddress->Country = $userProfile->getCountry();
            $ownerAddress->PostalCode = $userProfile->getAreaCode();
            $ownerAddress->Region = $userProfile->getState();
            $ownerAddress->City = $userProfile->getCity();
            $ownerAddress->AddressLine1 = $userProfile->getAddressLine1();
            $ownerAddress->AddressLine2 = $userProfile->getAddressLine2();
            $newBankAccount->OwnerAddress = $ownerAddress;
        }

        return $newBankAccount;
    }

    public function createBankAccountFromArray(array $bankAccount, string $userType = ApiConstants::ROLE_RESTAURANT_USER): ? BankAccount
    {
        $newBankAccount = new \MangoPay\BankAccount();

        $newBankAccount->Type = 'IBAN';
        $newBankAccount->Details = new BankAccountDetailsIBAN();
        $newBankAccount->Details->IBAN = $bankAccount['IBAN'];
        $newBankAccount->Details->BIC = $bankAccount['BIC'];
        $newBankAccount->OwnerName = $bankAccount['OwnerName'];

        $ownerAddress = new Address();
        $ownerAddress->Country = $bankAccount['Country'];
        $ownerAddress->PostalCode = $bankAccount['PostalCode'];
        $ownerAddress->Region = $bankAccount['Region'];
        $ownerAddress->City = $bankAccount['City'];
        $ownerAddress->AddressLine1 = $bankAccount['AddressLine1'];
        $ownerAddress->AddressLine2 = $bankAccount['AddressLine2'];

        $newBankAccount->OwnerAddress = $ownerAddress;

        return $newBankAccount;
    }

    /**
     * Remote call to create a BankAccount for the specified MangoPayUser.
     *
     * @param int         $mangopayUserID
     * @param BankAccount $bankAccount
     *
     * @return BankAccount|null
     */
    public function doCreateBankAccount(int $mangopayUserID, BankAccount $bankAccount): ?BankAccount
    {
        return $this->mangopayApi->Users->CreateBankAccount($mangopayUserID, $bankAccount);
    }

    public function getBankAccount(int $mangopayUserID, int $bankAccountID): BankAccount
    {
        return $this->mangopayApi->Users->GetBankAccount($mangopayUserID, $bankAccountID);
    }
}
