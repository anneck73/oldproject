<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\MangopayBundle\Services;

use Doctrine\ORM\EntityManager;
use MangoPay\Libraries\Exception as MangopayException;
use MangoPay\Libraries\ResponseException as MangopayResponseException;
use MangoPay\MangoPayApi;
use MangoPay\PayOut;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
use Mealmatch\ApiBundle\Exceptions\MealmatchException;
use Mealmatch\ApiBundle\Services\RestaurantService;
use MMUserBundle\Entity\MMUser;
use MMUserBundle\Entity\MMUserPaymentProfile;
use Psr\Log\LoggerInterface as Logger;
use Symfony\Component\Translation\Translator;

class MangopayPayOutService extends BaseMangopayService
{
    /**
     * @var MangopayApi
     */
    private $mangopayApi;
    /**
     * @var RestaurantService
     */
    private $restaurantService;

    public function __construct(
        Logger $logger,
        EntityManager $entityManager,
        Translator $translator,
        MangopayApiService $mangopayApiService,
        RestaurantService $restaurantService)
    {
        parent::__construct($logger, $entityManager, $translator);
        $this->mangopayApi = $mangopayApiService->getMangopayApi();
        $this->restaurantService = $restaurantService;
    }

    /**
     * Creates a mangopay PayOut to the bank account connected to the host of the mealticket.
     *
     * @param BaseMealTicket $mealTicket
     *
     * @throws MealmatchException
     *
     * @return PayOut the mangopay PayOut object
     */
    public function createPayOutToHostBankwire(BaseMealTicket $mealTicket): PayOut
    {
        /** @var MMUser $host */
        $host = $mealTicket->getHost();
        /** @var MMUserPaymentProfile $paymentProfile */
        $paymentProfile = $host->getPaymentProfile();

        $valid = $this->restaurantService->isPaymentProfilePayoutValid($paymentProfile);
        if (!$valid) {
            throw new MealmatchException('Payment profile of '.$host->getUsername().' is not valid for Payout!');
        }

        // We have stored mangopay ID's...
        $hostMangoPayId = $host->getPaymentProfile()->getMangopayID();
        $hostWalletId = $host->getPaymentProfile()->getMangopayWalletID();
        $hostBankAccountID = $host->getPaymentProfile()->getMangopayBankAccountId();

        // Payout mealticket price MINUS mealmatch fee PLUS taxes
        // Taxes
        $mealTicketTaxRate = $mealTicket->getHost()->getRestaurantProfile()->getTaxRate();
        // 10.00€ Meal, 0.19 Tax = 1.90€. Cent value of calc: 1.90 * 100 = 190cent.
        $mealTicketTaxesInCent = (int) ($mealTicket->getTotalPrice() * $mealTicketTaxRate) * 100;
        // Final calc, amount to payout to restaurant
        $payOutAmount = (int) $mealTicket->getPriceInCent() - ($mealTicket->getMmFee() * 100);

        /** @var PayOut $payOut */
        $payOut = new \MangoPay\PayOut();
        $payOut->AuthorId = $hostMangoPayId;
        $payOut->Tag = $mealTicket->getNumber();
        $payOut->DebitedWalletId = $hostWalletId;
        $payOut->DebitedFunds = new \MangoPay\Money();
        $payOut->DebitedFunds->Currency = 'EUR';
        $payOut->DebitedFunds->Amount = $payOutAmount;
        $payOut->Fees = new \MangoPay\Money();
        $payOut->Fees->Currency = 'EUR';
        $payOut->Fees->Amount = 0;
        $payOut->PaymentType = \MangoPay\PayOutPaymentType::BankWire;
        $payOut->MeanOfPaymentDetails = new \MangoPay\PayOutPaymentDetailsBankWire();
        $payOut->MeanOfPaymentDetails->BankAccountId = $hostBankAccountID;
        $payOut->MeanOfPaymentDetails->BankWireRef = 'Mealmatch Restaurant-Meal '.$mealTicket->getNumber();

        $this->logger->info('Created PayOut for MT'.$mealTicket->getNumber().
        'PayOut amount: '.$payOutAmount.'including taxes: '.$mealTicketTaxesInCent.'\n'.
        'MangopayPayOutObj: '.json_encode($payOut));

        return $payOut;
    }

    /**
     * Do remote call to mangopay API PayOuts->create($payOut) and return the result.
     *
     * @param PayOut $payOut
     *
     * @return PayOut the payout consumed by the mangopay api, in case of error the payout parameter is returned
     */
    public function doCreatePayOut(PayOut $payOut): PayOut
    {
        try {
            $newPayout = $this->mangopayApi->PayOuts->Create($payOut);
            $this->logger->alert('createPayOut ResultMessage: '.$payOut->ResultMessage);

            return $newPayout;
        } catch (MangopayResponseException $responseException) {
            $this->logger->error('MangoPay PayOut: Failed with ResponseException: '.$responseException->getMessage());
        } catch (MangopayException $exception) {
            $this->logger->error('MangoPay PayOut: Failed with LibraryException: '.$exception->getMessage());
        }

        return $payOut;
    }

    /**
     * Creates a PayOut object for the owner (PaymentProfileData) to send money from the wallet to the bank account.
     *
     * @param MMUserPaymentProfile $ownerPaymentProfile
     * @param int                  $amount
     *
     * @return PayOut
     */
    public function createPayOutToOwnerBankwire(MMUserPaymentProfile $ownerPaymentProfile, int $amount): PayOut
    {
        $authorId = $ownerPaymentProfile->getMangopayID();
        /** @var PayOut $payOut */
        $payOut = new \MangoPay\PayOut();
        $payOut->AuthorId = $authorId;
        $payOut->Tag = 'Owner Payout';
        $payOut->DebitedWalletId = $ownerPaymentProfile->getMangopayWalletID();
        $payOut->DebitedFunds = new \MangoPay\Money();
        $payOut->DebitedFunds->Currency = 'EUR';
        $payOut->DebitedFunds->Amount = $amount;
        $payOut->Fees = new \MangoPay\Money();
        $payOut->Fees->Currency = 'EUR';
        $payOut->Fees->Amount = 0;
        $payOut->PaymentType = \MangoPay\PayOutPaymentType::BankWire;
        $payOut->MeanOfPaymentDetails = new \MangoPay\PayOutPaymentDetailsBankWire();
        $payOut->MeanOfPaymentDetails->BankAccountId = $ownerPaymentProfile->getMangopayBankAccountId();
        $payOut->MeanOfPaymentDetails->BankWireRef = 'Mealmatch Auszahlung an Bankkonto ';
        $this->logger->info('Created Owner PayOut to BankAccount via Bankwire with amount: '.$amount.'\n'.
            'MangopayPayOutObj: '.json_encode($payOut));

        return $payOut;
    }
}
