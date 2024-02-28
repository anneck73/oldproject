<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\MangopayBundle\Services;

use Doctrine\ORM\EntityManager;
use Exception;
use MangoPay\BankAccount;
use MangoPay\Libraries\Exception as LibraryException;
use MangoPay\MangoPayApi;
use MangoPay\Money;
use MangoPay\PayOut;
use MangoPay\PayOutPaymentDetailsBankWire;
use MangoPay\PayOutPaymentType;
use MangoPay\Transfer;
use MangoPay\User as MangopayUser;
use MangoPay\UserLegal;
use MangoPay\UserNatural;
use MangoPay\Wallet;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
use MMUserBundle\Entity\MMUser;
use Psr\Log\LoggerInterface as Logger;
use Symfony\Component\Translation\Translator;

/**
 * The public service "PublicMangopayService" exposes the MangopayApi service with Mealmatch specific methods.
 */
class PublicMangopayService extends BaseMangopayService
{
    /** @var MangoPayApi $mangopayApi */
    private $mangopayApi;

    /** @var MangopayApiService $apiService */
    private $apiService;

    /** @var MangopayPayOutService $payOutService */
    private $payOutService;

    /** @var MangopayPayInService $payInService */
    private $payInService;

    /** @var MangopayUserService $userService */
    private $userService;

    /** @var MangopayBankAccountService $bankAccountService */
    private $bankAccountService;

    /** @var MangopayWalletService $walletService */
    private $walletService;

    /**
     * PublicMangopayService constructor.
     *
     * @param Logger             $logger
     * @param EntityManager      $entityManager
     * @param Translator         $translator
     * @param MangopayApiService $apiService
     */
    public function __construct(
        Logger $logger,
        EntityManager $entityManager,
        Translator $translator,
        MangopayApiService $apiService)
    {
        parent::__construct($logger, $entityManager, $translator);
        /* @var MangoPayApi mangopayApi */
        $this->mangopayApi = $apiService->getMangopayApi();
        $this->apiService = $apiService;
    }

    /**
     * Remote call using internal mangopay api to create a UserNatural.
     *
     * @param UserNatural $userNatural
     *
     * @return UserNatural|null
     */
    public function doCreateUserNatural(UserNatural $userNatural): ?UserNatural
    {
        try {
            /** @var UserNatural $result */
            $result = $this->mangopayApi->Users->Create($userNatural);
        } catch (LibraryException $libraryExeption) {
            $this->logger->error('Failed to create remote UserNatural: '.$libraryExeption->getMessage());
        }
        $this->logger->alert('doCreateUserNatural: '.$result->Id);

        return $result;
    }

    public function doCreateUserLegal(UserLegal $userLegal): ?UserLegal
    {
        try {
            /** @var UserLegal $createdUserLegal */
            $createdUserLegal = $this->mangopayApi->Users->Create($userLegal);
        } catch (LibraryException $libraryExeption) {
            $this->logger->error('Failed to create remote UserLegal: '.$libraryExeption->getMessage());
        }
        $this->logger->alert('doCreateUserNatural: '.$createdUserLegal->Id);

        return $createdUserLegal;
    }

    /**
     * Create a UserNatural from a specific mealmatch user.
     *
     * @param MMUser $user
     *
     * @return UserNatural
     */
    public function createUserNatural(MMUser $user): UserNatural
    {
        /** @var UserNatural $userNatural */
        $userNatural = new UserNatural();
        $userNatural->Email = $user->getEmail();
        $userNatural->FirstName = $user->getUsername();
        $userNatural->LastName = $user->getUsername();
        $userNatural->PersonType = 'NATURAL';
        $userNatural->Birthday = $user->getProfile()->getBirthday()->getTimestamp();
        $userNatural->Nationality = $user->getProfile()->getCountry();
        $userNatural->CountryOfResidence = $user->getProfile()->getCountry();

        return $userNatural;
    }

    /**
     * Returns the mangopay user object specified by its ID.
     *
     * @param int $mangopayID
     *
     * @return MangopayUser|null
     */
    public function doUsersGet(int $mangopayID): ?MangopayUser
    {
        return $this->mangopayApi->Users->Get($mangopayID);
    }

    /**
     * Returns the amount of the first wallet of the mangopay user account.
     *
     * @param MMUser $user
     *
     * @return int
     */
    public function getFirstWalletBalance(MMUser $user): int
    {
        $userId = $user->getPaymentProfile()->getMangopayID();
        $wallets = $this->mangopayApi->Users->GetWallets($userId);
        /** @var Wallet $firstWallet */
        $firstWallet = $wallets[0];

        return $firstWallet->Balance->Amount;
    }

    /**
     * @param MMUser $user
     *
     * @return bool
     */
    public function validateUserCanReceivePayin(MMUser $user): bool
    {
        $this->logger->debug('PublicMangopayService:validateUserCanReceivePayin('.$user->getId().')');
        $mangoPayUserID = $user->getPaymentProfile()->getMangopayID();
        $this->logger->debug('validate Mangopay UserID: '.$mangoPayUserID);
        $mangoPayWalletID = $user->getPaymentProfile()->getMangopayWalletID();
        $this->logger->debug('validate Mangopay WalletID('.$mangoPayWalletID.')');
        try {
            /** @var Wallet $wallet */
            $wallet = $this->mangopayApi->Wallets->Get($mangoPayWalletID);
            $walletOK = false;
            if (null !== $wallet) {
                $this->logger->debug('Mangopay WalletID('.$wallet->Id.') exists!');
                $walletOK = true;
            }
            if ($walletOK) {
                return true;
            }

            return false;
        } catch (LibraryException $libraryException) {
            $this->logger->error('Failed to validateUserCanReceivePayin('.$mangoPayUserID.'): '.$libraryException->getMessage());

            return false;
        } catch (Exception $exception) {
            $this->logger->error('Failed to validateUserCanReceivePayin('.$mangoPayUserID.'): '.$exception->getMessage());

            return false;
        }
    }

    public function validateUserCanReceiveBankwirePayout(MMUser $user): bool
    {
        $this->logger->debug('PublicMangopayService:validateUserCanReceiveBankwirePayout('.$user->getId().')');
        $mangoPayUserID = $user->getPaymentProfile()->getMangopayID();
        $this->logger->debug('validate Mangopay UserID: '.$mangoPayUserID);
        $mangoPayWalletID = $user->getPaymentProfile()->getMangopayWalletID();
        $this->logger->debug('validate Mangopay WalletID('.$mangoPayWalletID.')');
        try {
            /** @var BankAccount $account */
            $account = $this->mangopayApi->Users->GetBankAccounts($mangoPayUserID)[0];
            $accountOK = false;
            if (null !== $account) {
                $this->logger->debug('Mangopay BankAccountID('.$account->Id.') exists!');
                $this->logger->debug('Mangopay BankAccountIBAN('.$account->Details->IBAN.')!');
                $accountOK = true;
            }
            if ($accountOK) {
                return true;
            }

            return false;
        } catch (LibraryException $libraryException) {
            $this->logger->error('Failed to validateUserCanBankwirePayout('.$mangoPayUserID.'): '.$libraryException->getMessage());

            return false;
        } catch (Exception $exception) {
            $this->logger->error('Failed to validateUserCanBankwirePayout('.$mangoPayUserID.'): '.$exception->getMessage());

            return false;
        }
    }

    /**
     * @param Transfer $transfer
     *
     * @return transfer - the result of the Transfer->create($transfer) call to mangopayApi
     */
    public function executeTransfer(Transfer $transfer): Transfer
    {
        try {
            $this->logger->debug('executeTransfer---------------------------------->START');
            $resultTransfer = $this->mangopayApi->Transfers->Create($transfer);
            $this->logger->debug('executeTransfer:createResult:'.json_encode($resultTransfer));
            $this->logger->debug('executeTransfer---------------------------------->END');

            return $resultTransfer;
        } catch (LibraryException $libraryException) {
            $this->logger->error('PublicMangopayService:executeTransfer LibraryException: '.$libraryException->getMessage());
            $this->logger->error('PublicMangopayService:executeTransfer LibraryException: '.$transfer->ResultMessage);

            return $transfer;
        } catch (Exception $exception) {
            $this->logger->error('PublicMangopayService:executeTransfer Exception: '.$exception->getMessage());
            $this->logger->error('PublicMangopayService:executeTransfer LibraryException: '.$transfer->ResultMessage);

            return $transfer;
        }
    }

    public function createTransferGuestToHostWallet(BaseMealTicket $mealTicket): Transfer
    {
        // parameter loggen
        $this->logger->debug('BEGIN-------------------doTransferGuestToHostWallet----------------------------------');
        $this->logger->debug('PublicMangopayService:createTransferGuestToHostWallet(MealticketId: '.$mealTicket->getId().')');
        $this->logger->debug('PublicMangopayService:createTransferGuestToHostWallet(MealticketStatus: '.$mealTicket->getStatus().')');
        $this->logger->debug('PublicMangopayService:createTransferGuestToHostWallet(MealticketPayInStatus: '.$mealTicket->getPayInStatus().')');
        $this->logger->debug('PublicMangopayService:createTransferGuestToHostWallet(Guest: '.$mealTicket->getGuest().')');
        $this->logger->debug('PublicMangopayService:createTransferGuestToHostWallet(Host: '.$mealTicket->getHost().')');

        $guestMangoPayUserID = $mealTicket->getGuest()->getPaymentProfile()->getMangopayID();
        $this->logger->debug("Guest Mangopay UserID($guestMangoPayUserID)");

        $guestMangoPayWalletID = $mealTicket->getGuest()->getPaymentProfile()->getMangopayWalletID();
        $this->logger->debug("Guest Mangopay WalletID($guestMangoPayWalletID)");

        $hostMangoPayUserID = $mealTicket->getHost()->getPaymentProfile()->getMangopayID();
        $this->logger->debug("Host Mangopay UserID($hostMangoPayUserID)");

        $hostMangoPayWalletID = $mealTicket->getHost()->getPaymentProfile()->getMangopayWalletID();
        $this->logger->debug('Host Mangopay WalletID('.$hostMangoPayWalletID.')');

        // Make a Mangopay Transfer Object DEBIT = Das belastete CREDIT = Dort wird gutgeschrieben
        $transfer = new Transfer();
        $transfer->Tag = $mealTicket->getNumber();
        $transfer->AuthorId = $mealTicket->getGuest()->getMangopayID();
        $transfer->CreditedUserId = $mealTicket->getHost()->getMangopayID();

        $transfer->DebitedFunds = new Money();
        $transfer->DebitedFunds->Currency = $mealTicket->getCurrency();
        $transfer->DebitedFunds->Amount = $mealTicket->getTotalPriceInCent();

        // 0 Fees during transfer
        $transfer->Fees = new Money();
        $transfer->Fees->Currency = 'EUR';
        $transfer->Fees->Amount = 0;

        $transfer->DebitedWalletId = $mealTicket->getGuest()->getMangopayWalletID();
        $transfer->CreditedWalletId = $mealTicket->getHost()->getMangopayWalletID();

        return $transfer;
    }

    public function getMangopayWalletHostBalanceInCent(BaseMealTicket $mealTicket): int
    {
        $walletID = $mealTicket->getHost()->getMangopayWalletID();
        $this->logger->debug('PublicMangopayService:getMangopayWalletHostBalanceInCent('.$walletID.')');

        /** @var Wallet $wallet */
        $wallet = $this->mangopayApi->Wallets->Get($walletID);
        $this->logger->debug('PublicMangopayService:getMangopayWalletHostBalanceInCent: WalletAmount: '.$wallet->Balance->Amount.' cent');

        return $wallet->Balance->Amount;
    }

    public function getMangopayWalletGuestBalanceInCent(BaseMealTicket $mealTicket): int
    {
        $walletID = $mealTicket->getGuest()->getMangopayWalletID();
        $this->logger->debug('PublicMangopayService:getMangopayWalletGuestBalanceInCent('.$walletID.')');

        /** @var Wallet $wallet */
        $wallet = $this->mangopayApi->Wallets->Get($walletID);
        $this->logger->debug('PublicMangopayService:getMangopayWalletGuestBalanceInCent: WalletAmount: '.$wallet->Balance->Amount.' cent');

        return $wallet->Balance->Amount;
    }

    public function createTransferCouponToGuestWallet(BaseMealTicket $mealTicket): ?Transfer
    {
        // The Mealmatch Coupon Wallet ID
        $mangopayCouponWalletID = $this->apiService->getCouponWalletID();
        /** @var Wallet */
        $couponWallet = $this->apiService->getMangopayApi()->Wallets->Get($mangopayCouponWalletID);
        $couponWalletOwnerID = $couponWallet->Owners[0];

        // parameter loggen
        $this->logger->debug('BEGIN-------------------createTransferCouponToGuestWallet----------------------------------');
        $this->logger->debug('---->(MealticketId: '.$mealTicket->getId().')');
        $this->logger->debug('---->(MealticketStatus: '.$mealTicket->getStatus().')');
        $this->logger->debug('---->(MealticketCurrency: '.$mealTicket->getCurrency().')');
        $this->logger->debug('---->(Guest: '.$mealTicket->getGuest()->getMangopayWalletID().')');
        $this->logger->debug('---->(CouponWallet: '.$mangopayCouponWalletID.')');
        $this->logger->debug('---->(CouponWalletOwner: '.$couponWalletOwnerID.')');
        $this->logger->debug('---->(CouponValue: '.
            $mealTicket->getCoupon()->getValue().')');
        $this->logger->debug('---->(CouponCurrency: '.
            $mealTicket->getCoupon()->getCurrency().')');

        $guestMangoPayUserID = $mealTicket->getGuest()->getPaymentProfile()->getMangopayID();
        $this->logger->debug("---->Guest Mangopay UserID($guestMangoPayUserID)");

        $guestMangoPayWalletID = $mealTicket->getGuest()->getPaymentProfile()->getMangopayWalletID();
        $this->logger->debug("---->Guest Mangopay WalletID($guestMangoPayWalletID)");

        // Make a Mangopay Transfer Object
        $transfer = new Transfer();
        $transfer->Tag = $mealTicket->getNumber();

        $transfer->DebitedFunds = new Money();
        $transfer->DebitedFunds->Currency = $mealTicket->getCurrency();
        // The value of the coupon IN CENT is transfered into the guest wallet
        $transfer->DebitedFunds->Amount = (int) $mealTicket->getCoupon()->getValue() * 100;

        // 0 Fees during transfer
        $transfer->Fees = new Money();
        $transfer->Fees->Currency = 'EUR';
        $transfer->Fees->Amount = 0;

        $transfer->AuthorId = $couponWalletOwnerID;
        // Debited = abgebucht
        $transfer->DebitedWalletId = $mangopayCouponWalletID;
        // Credited = gutgeschrieben
        $transfer->CreditedWalletId = $guestMangoPayWalletID;

        return $transfer;
    }

    public function getMangopayPayOutService(): MangopayPayOutService
    {
        return $this->payOutService;
    }

    /**
     * @return MangopayPayInService
     */
    public function getMangopayPayInService(): MangopayPayInService
    {
        return $this->payInService;
    }

    public function getExecutedHostPayOut(PayOut $payOut)
    {
        return $this->mangopayApi->PayOuts->Get($payOut->Id);
    }

    public function createPayOutToHostBankAccount(BaseMealTicket $mealTicket)
    {
        /** @var PayOut $PayOut */
        $PayOut = new PayOut();
        $PayOut->AuthorId = $mealTicket->getHost()->getMangopayID();
        $PayOut->DebitedWalletId = $mealTicket->getHost()->getMangopayWalletID();
        $PayOut->DebitedFunds = new Money();
        $PayOut->DebitedFunds->Currency = 'EUR';
        $PayOut->DebitedFunds->Amount = $mealTicket->getPriceInCent() - ($mealTicket->getPriceInCent() * 0.15);
        $PayOut->Fees = new Money();
        $PayOut->Fees->Currency = 'EUR';
        $PayOut->Fees->Amount = 0;
        $PayOut->PaymentType = PayOutPaymentType::BankWire;
        $PayOut->MeanOfPaymentDetails = new PayOutPaymentDetailsBankWire();
        $PayOut->MeanOfPaymentDetails->BankAccountId = $mealTicket->getHost()->getPaymentProfile()->getMangopayBankAccountId();

        return $this->mangopayApi->PayOuts->Create($PayOut);
    }

    public function setPayInService(MangopayPayInService $payInService)
    {
        $this->payInService = $payInService;
    }

    public function setPayOutService(MangopayPayOutService $payOutService)
    {
        $this->payOutService = $payOutService;
    }

    public function setUserService(MangopayUserService $userService): void
    {
        $this->userService = $userService;
    }

    /**
     * @return MangopayUserService
     */
    public function getMangopayUserService(): MangopayUserService
    {
        return $this->userService;
    }

    public function setBankAccountService(MangopayBankAccountService $bankAccountService)
    {
        $this->bankAccountService = $bankAccountService;
    }

    /**
     * @return MangopayUserService
     */
    public function getMangopayBankAccountService(): MangopayBankAccountService
    {
        return $this->bankAccountService;
    }

    /**
     * @return MangopayWalletService
     */
    public function getMangopayWalletService(): MangopayWalletService
    {
        return $this->walletService;
    }

    /**
     * @param MangopayWalletService $walletService
     */
    public function setWalletService(MangopayWalletService $walletService): void
    {
        $this->walletService = $walletService;
    }
}
