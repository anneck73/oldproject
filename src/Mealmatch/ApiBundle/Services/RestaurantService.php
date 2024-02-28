<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use MangoPay\Address;
use MangoPay\BankAccount;
use MangoPay\BankAccountDetailsIBAN;
use MangoPay\Libraries\Exception as LibraryException;
use MangoPay\MangoPayApi;
use MangoPay\UserLegal;
use MangoPay\Wallet;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Mealmatch\ApiBundle\Exceptions\MealmatchException;
use Mealmatch\MangopayBundle\Services\MangopayApiService;
use MMUserBundle\Entity\MMRestaurantProfile;
use MMUserBundle\Entity\MMUser;
use MMUserBundle\Entity\MMUserPaymentProfile;
use MMUserBundle\Entity\MMUserProfile;
use MMUserBundle\Entity\RestaurantImage;
use Monolog\Logger;
use Symfony\Component\Translation\Translator;

/**
 * @todo: Finish PHPDoc!
 * The RestaurantService ...
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class RestaurantService
{
    private $logger;
    private $entityManager;
    private $translator;
    private $mealTicketService;
    /**
     * @var MangoPayApi
     */
    private $mangopayApi;
    private $mangopayApiService;

    /**
     * RestaurantService constructor.
     *
     * @param Logger        $logger
     * @param EntityManager $entityManager
     * @param Translator    $translator
     */
    public function __construct(
        Logger $logger,
        EntityManager $entityManager,
        Translator $translator,
        MealTicketService $mealTicketService,
        MangopayApiService $mangopayApiService
    ) {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->mealTicketService = $mealTicketService;
        $this->mangopayApi = $mangopayApiService->getMangopayApi();
        $this->mangopayApiService = $mangopayApiService;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param ProMeal $proMeal
     * @param MMUser  $user
     *
     * @throws MealmatchException
     *
     * @return Collection
     */
    public function getSelectedMealOffers(ProMeal $proMeal, MMUser $user): Collection
    {
        $selectedMealOffers = array();
        // The Mealticket is the only one who "knows" which MealOffer was selected.
        // WEBAPP-111: It is possible to get n-BaseMealTickets for one guest
        $allTickets = new ArrayCollection($this->mealTicketService->findAllByMealAndUser($proMeal, $user));
        /** @var BaseMealTicket $ticket */
        foreach ($allTickets as $ticket) {
            $selectedMealOffers[] = $ticket->getSelectedMealOffer();
        }

        return new ArrayCollection($selectedMealOffers);
    }

    /**
     * Returns a RestaurantImage of the Restaurantprofile of the specified $proMeal.
     *
     * To have different pictures for the meal "head" the method always returns "the next picture in row" from the
     * collection of RestaurantImages's in the RestaurantProfile.
     *
     * @param ProMeal $proMeal the ProMeal to get a RestaurantImage for
     *
     * @throws MealmatchException if there are null restaurant images on the restaurant profile
     *
     * @return RestaurantImage the RestaurantImage of the Restaurantprofile associated to the specified $proMeal
     */
    public function getPicture(ProMeal $proMeal): RestaurantImage
    {
        $restaurant = $proMeal->getHost();
        /** @noinspection NullPointerExceptionInspection - there is always a RestaurantProfile due to the way we create
         * users! */
        $allPics = $restaurant->getRestaurantProfile()->getPictures();
        $allPicsMax = $allPics->count() - 1;
        $randomPicIdx = random_int(0, $allPicsMax);

        return $allPics->getValues()[$randomPicIdx];
    }

    public function isPaymentProfilePayoutValid(MMUserPaymentProfile $paymentProfile): bool
    {
        $validationC = new ArrayCollection(array(
                'MangopayID' => false,
                'MangopayWalletID' => false,
                'MangopayBankaccountID' => false,
            )
        );

        if ($paymentProfile->getMangopayID() > 0) {
            $validationC->set('MangopayID', true);
        }

        if ($paymentProfile->getMangopayWalletID() > 0) {
            $validationC->set('MangopayWalletID', true);
        }

        if ($paymentProfile->getMangopayBankAccountId() > 0) {
            $validationC->set('MangopayBankaccountID', true);
        }
        // Default to true, one failed check will set it to false.
        $validation = true;
        // Now check everyhting and determine validity ...
        foreach ($validationC as $key => $value) {
            // false value, validation = false
            if (false === $value) {
                $this->logger->addError("Validation of RestaurantProfile failed. '$key' was null");
                $validation = false;
            }
        }

        return $validation;
    }

    public function getOrCreateMangopayIDs(MMUser $host): bool
    {
        $this->logger->debug('getOrCreateMangopayIDs->'.$host->getRestaurantProfile()->getCompany());

        try {
            $hostLegal = $this->getOrCreateHostLegal($host);
            $this->logger->debug('Using hostLegal: '.$hostLegal->Id);

            $hostWallet = $this->getOrCreateWallet($host, $hostLegal);
            $this->logger->debug('Using hostWallet: '.$hostWallet->Id);

            $hostBankAccount = $this->getOrCreateBankAccount($host);
            $this->logger->debug('Using hostBankaccount: '.$hostBankAccount->Id);

            return true;
        } catch (LibraryException $libraryException) {
            $this->logger->addError('Failed to create BankAccount for Host ('.$host->getId().'): '
                .$libraryException->getMessage());

            return false;
        } catch (\Exception $mangoException) {
            $this->logger->addError('Failed to create BankAccount for Host ('.$host->getId().'): '
                .$mangoException->getMessage());

            return false;
        }
    }

    /**
     * @todo: reduce the complexity of this method!
     *
     * Validates if a RestaurantProfile is valid.
     *
     * Validity is determined through the required data for the creation of a ProMeal.
     *
     * @param MMRestaurantProfile $restaurantProfile
     *
     * @return bool true if valid, else false
     */
    public function isRestaurantProfileValid(MMRestaurantProfile $restaurantProfile): bool
    {
        // Validations are initially false, they all need to be set to true ...
        // unless you dont want to validate, then default true = no validation
        $validationC = new ArrayCollection(
            array(
                'company' => false, // benötigt für MealTickets
                'taxRate' => false, // benötigt für Mealpreise
                'commercialRegisterNumber' => false,
                'name' => true,
                'payPal' => true, // benötigt für PayPal Payments
                'authorizedRepresentative' => false,
                'contactAddress' => false,
                'contactEmail' => false,
                'contactPhone' => false,
                'address' => false, // benötigt für die Anzeige in der Suche
                'pictures' => false, // mind. 1 Bild
                'currency' => false, // required by Mealpreise
                'taxID' => false, // required by MealTickets
                'iban' => false, // MealTicket
            )
        );

        //Firma/Inhaber
        if (null !== $restaurantProfile->getCompany()) {
            $validationC->set('company', true);
        }
        //Handelsregistrer Nummer
        if (null !== $restaurantProfile->getCommercialRegisterNumber()) {
            $validationC->set('commercialRegisterNumber', true);
        }
        //MwSt
        if (null !== $restaurantProfile->getTaxRate()) {
            $validationC->set('taxRate', true);
        }
        //Restaurantname
        if (null !== $restaurantProfile->getName()) {
            $validationC->set('name', true);
        }
        //UmsatzsteuerID
        if (null !== $restaurantProfile->getTaxID()) {
            $validationC->set('taxID', true);
        }
        //PayPal Adresse
        if (null !== $restaurantProfile->getPayPalEmail()) {
            $validationC->set('payPal', true);
        }
        //Vertretungsberechtigte Person
        if (null !== $restaurantProfile->getAuthorizedRepresentative()) {
            $validationC->set('authorizedRepresentative', true);
        }
        //Kontaktadresse
        if (null !== $restaurantProfile->getContactAddress()) {
            $validationC->set('contactAddress', true);
        }
        //Email
        if (null !== $restaurantProfile->getContactEmail()) {
            $validationC->set('contactEmail', true);
        }
        //Telefon
        if (null !== $restaurantProfile->getContactPhone()) {
            $validationC->set('contactPhone', true);
        }
        //Standort
        if ($restaurantProfile->getAddresses()->count() > 0) {
            $validationC->set('address', true);
        }
        //nur 1 Bild zur Pflicht machen
        if ($restaurantProfile->getPictures()->count() >= 1) {
            $validationC->set('pictures', true);
        }
        //Währung
        if (null !== $restaurantProfile->getDefaultCurrency()) {
            $validationC->set('currency', true);
        }
        // IBAN
        if (null !== $restaurantProfile->getBankIBAN()) {
            $validationC->set('iban', true);
        }

        // Default to true, one failed check will set it to false.
        $validation = true;
        // Now check everyhting and determine validity ...
        foreach ($validationC as $key => $value) {
            // false value, validation = false
            if (false === $value) {
                $this->logger->addError("Validation of RestaurantProfile failed. '$key' was null");
                $validation = false;
            }
        }

        return $validation;
    }

    /**
     * Checks if the restaurant profile has a MangopayId, MangopayWalletId and a MangopayBankAccountId.
     *
     * @param MMUserPaymentProfile $mmUserPaymentProfile
     *
     * @return bool true if valid, else false
     */
    public function hasRestaurantProfileNeededMangopayIds(MMUserPaymentProfile $mmUserPaymentProfile): bool
    {
        $validationC = new ArrayCollection(
            array(
                'mangopayID' => false,
                'mangopayWalletID' => false,
                'mangopayBankAccountID' => false,
            )
        );

        if (null !== $mmUserPaymentProfile->getMangopayID()) {
            $validationC->set('mangopayID', true);
        }
        if (null !== $mmUserPaymentProfile->getMangopayWalletID()) {
            $validationC->set('mangopayWalletID', true);
        }
        if (null !== $mmUserPaymentProfile->getMangopayBankAccountId()) {
            $validationC->set('mangopayBankAccountID', true);
        }

        // Default to true, one failed check will set it to false.
        $validation = true;
        // Now check everyhting and determine validity ...
        foreach ($validationC as $key => $value) {
            // false value, validation = false
            if (false === $value) {
                $this->logger->addError("Validation of RestaurantPaymentProfile failed. '$key' was null");
                $validation = false;
            }
        }

        return $validation;
    }

    public function isUserProfileValid(MMUserProfile $userProfile): bool
    {
        // Validations are initially false, they all need to be set to true ...
        // unless you dont want to validate, then default true = no validation
        $validationC = new ArrayCollection(
            array(
                'Birthday' => false, // benötigt für MealTickets Payout to create Bankaccount
                'Country' => false, // benötigt für MealTickets Payout to create Bankaccount
                'Nationality' => false, // benötigt für MealTickets Payout to create Bankaccount
            )
        );

        if (null !== $userProfile->getBirthday()) {
            $validationC->set('Birthday', true);
        }

        if (null !== $userProfile->getCountry()) {
            $validationC->set('Country', true);
        }

        if (null !== $userProfile->getNationality()) {
            $validationC->set('Nationality', true);
        }
        // Default to true, one failed check will set it to false.
        $validation = true;
        // Now check everyhting and determine validity ...
        foreach ($validationC as $key => $value) {
            // false value, validation = false
            if (false === $value) {
                $this->logger->addError("Validation of RestaurantProfile failed. '$key' was null");
                $validation = false;
            }
        }

        return $validation;
    }

    public function checkBankAccountDataForChanges(MMUser $host): bool
    {
        // Need Hostprofile Bank IBAN
        $hostProfileBankIban = $host->getPaymentProfile()->getIban();
        // Need Mangopay Bankaccount IBAN
        $mangopayUserId = $host->getMangopayID();
        $mangopayBankAccountId = $host->getPaymentProfile()->getMangopayBankAccountId();

        $mangopayBankAccountIban = $this->mangopayApi->Users->GetBankAccount(
            $mangopayUserId, $mangopayBankAccountId);

        return $hostProfileBankIban !== $mangopayBankAccountIban;
    }

    public function deactivateBankAccount(MMUser $user): void
    {
        $bankAccount = $this->mangopayApi->Users->GetBankAccount($user->getMangopayID(),
            $user->getPaymentProfile()->getMangopayBankAccountId());
        $bankAccount->Active = false;
        $this->mangopayApi->Users->UpdateBankAccount($user->getMangopayID(), $bankAccount);
    }

    public function createMangopayBankAccount(MMUser $host): void
    {
        $hostRestaurantProfile = $host->getRestaurantProfile();
        $hostMangoPayId = $host->getPaymentProfile()->getMangopayID();

        $BankAccount = new \MangoPay\BankAccount();
        $BankAccount->Type = 'IBAN';
        $BankAccount->Details = new BankAccountDetailsIBAN();
        $BankIBAN = $hostRestaurantProfile->getBankIBAN();
        $address = $hostRestaurantProfile->getAddress();
        $ownerName = $hostRestaurantProfile->getCompany();

        $BankAccount->Details->IBAN = $BankIBAN;
        $BankAccount->Details->BIC = $host->getPaymentProfile()->getBic();
        $BankAccount->OwnerName = $ownerName;
        $address = array(
            'AddressLine1' => $address->getStreetName(),
            'AddressLine2' => $address->getExtraLine1(),
            'City' => $address->getCity(),
            'Region' => '',
            'PostalCode' => $address->getPostalCode(),
            'Country' => $address->getCountryCode(),
        );
        $BankAccount->OwnerAddress = $address;

        $this->logger->addAlert('createMangopayBankAccount: Host ('.$host->getId().')');
        $this->logger->addAlert('Host-MangoPayID: '.$host->getPaymentProfile()->getMangopayID());
        $this->logger->addAlert('Host-IBAN: '.$host->getRestaurantProfile()->getBankIBAN());
        $this->logger->addAlert('Host-Company: '.$host->getRestaurantProfile()->getCompany());
        $this->logger->addAlert('Host-Address: '.$host->getRestaurantProfile()->getAddress());
        // Creating Bank account for Restaurant
        $newBankAccount = $this->mangopayApi->Users->CreateBankAccount($hostMangoPayId, $BankAccount);
        $this->logger->addAlert('NewBankAccountObj:'.json_encode($newBankAccount));
        $host->getPaymentProfile()->setMangopayBankAccountId($newBankAccount->Id);

        $this->logger->addAlert('createMangopayBankAccount:  Host('.$host->getId().'): created succesfully'
            .$newBankAccount->Id);
    }

    /**
     * @param MMUser $host
     *
     * @throws MealmatchException
     *
     * @return UserLegal|\MangoPay\UserNatural
     */
    protected function getOrCreateHostLegal(MMUser $host): UserLegal
    {
        // Check if host LegalUser already exists.
        if (null !== $host->getMangopayID()) {
            $currentHostLegal = $this->mangopayApi->Users->Get($host->getMangopayID());
            $this->logger->addAlert('Using existing UserLegal for Host('.$host->getId().'): '
                .$currentHostLegal->Id);

            return $currentHostLegal;
        }
        // Create a UserLegal object from $host
        /* @var UserLegal $currentHostLegal */
        $newHostLegal = $this->mangopayApiService->getMangopayUserService()->createUserLegalFrom($host);
        // Remote call to mangopay to create the specified UserLegal
        try {
            $createdNewHostLegal = $this->mangopayApi->Users->Create($newHostLegal);
            // set the new mangopay user ID in the payment profile for the host.
            $host->getPaymentProfile()->setMangopayID($createdNewHostLegal->Id);
            $this->logger->addAlert('Succesfully created new UserLegal for Host('.$host->getId().'): '
                .$createdNewHostLegal->Id);

            return $createdNewHostLegal;
        } catch (LibraryException $exception) {
            $errMsg = 'Failed to created new UserLegal for Host('.$host->getId().'): '.$exception->getMessage();
            $this->logger->error($errMsg);
            throw new MealmatchException($errMsg);
        }
    }

    /**
     * @param MMUser $host
     * @param $currentHostLegal
     *
     * @return Wallet
     */
    protected function getOrCreateWallet(MMUser $host, $currentHostLegal): Wallet
    {
        // In case Wallet Id is already there ...
        if (null !== $host->getMangopayWalletID()) {
            $existingWallet = $this->mangopayApi->Wallets->Get($host->getMangopayWalletID());
            $this->logger->addAlert('Using existing Wallet for Host('.$host->getId().'): '
                .$existingWallet->Id);
            // Return existing
            return $existingWallet;
        }
        // Create a new Wallet
        $Wallet = new Wallet($currentHostLegal->Id);
        $Wallet->Owners = array($currentHostLegal->Id);
        $Wallet->Description = 'Mealmatch Wallet '.$host->getRestaurantProfile()->getCompany();
        $Wallet->Currency = 'EUR';

        $newWallet = $this->mangopayApi->Wallets->Create($Wallet);
        // set the new mangopay user ID in the payment profile for the host.
        $host->getPaymentProfile()->setMangopayWalletID($newWallet->Id);

        $this->logger->addAlert('Succesfully created new Wallet for Host('.$host->getId().'): '
            .$newWallet->Id);

        // Return new wallet
        return $newWallet;
    }

    /**
     * @param MMUser $host
     */
    protected function getOrCreateBankAccount(MMUser $host): BankAccount
    {
        // In case Bankaccount ID is already there ...
        if (null !== $host->getPaymentProfile()->getMangopayBankAccountId()) {
            $existingBankAccount = $this->mangopayApi->Users
                ->GetBankAccount($host->getMangopayID(), $host->getPaymentProfile()->getMangopayBankAccountId());
            $this->logger->addAlert('Using existing Bankaccount for Host('.$host->getId().'): '
                .$existingBankAccount->Id);

            return $existingBankAccount;
        }

        $hostRestaurantProfile = $host->getRestaurantProfile();
        $hostMangoPayId = $host->getPaymentProfile()->getMangopayID();
        $bankIBAN = $hostRestaurantProfile->getBankIBAN();
        $ownerName = $hostRestaurantProfile->getCompany();

        $prepareBankAccount = new \MangoPay\BankAccount();
        $prepareBankAccount->Type = 'IBAN';
        $prepareBankAccount->Details = new BankAccountDetailsIBAN();
        $prepareBankAccount->Details->IBAN = $bankIBAN;
        $prepareBankAccount->Details->BIC = $host->getPaymentProfile()->getBic();
        $prepareBankAccount->OwnerName = $ownerName;

        $ownerAddress = new Address();
        $ownerAddress->AddressLine1 = $host->getRestaurantProfile()->getLegalRepresentativeAddressLine1();
        $ownerAddress->AddressLine2 = $host->getRestaurantProfile()->getLegalRepresentativeAddressLine2();
        $ownerAddress->City = $host->getRestaurantProfile()->getLegalRepresentativeCity();
        $ownerAddress->Region = $host->getRestaurantProfile()->getLegalRepresentativeRegion();
        $ownerAddress->PostalCode = $host->getRestaurantProfile()->getLegalRepresentativePostalCode();
        $ownerAddress->Country = $host->getRestaurantProfile()->getCountry();
        $prepareBankAccount->OwnerAddress = $ownerAddress;

        $this->logger->addDebug('Trying to create new BankAccount using: \n'.json_encode($prepareBankAccount));
        // Creating Bank account for Restaurant

        $newBankAccount = $this->mangopayApi->Users->CreateBankAccount($hostMangoPayId, $prepareBankAccount);

        $host->getPaymentProfile()->setMangopayBankAccountId($newBankAccount->Id);
        $this->logger->addAlert('Succesfully created new BankAccount for Host('.$host->getId().'): '
            .$newBankAccount->Id);

        return $newBankAccount;
    }
}
