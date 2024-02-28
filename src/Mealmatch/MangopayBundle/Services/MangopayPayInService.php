<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\MangopayBundle\Services;

use Doctrine\ORM\EntityManager;
use MangoPay\MangoPayApi;
use MangoPay\Money;
use MangoPay\PayIn;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
use Psr\Log\LoggerInterface as Logger;
use Symfony\Component\Translation\Translator;

class MangopayPayInService extends BaseMangopayService
{
    /**
     * @var MangoPayApi
     */
    private $mangopayApi;

    /** @var MangopayApiService $mangopayApiService */
    private $mangopayApiService;
    /**
     * @var MangopayWalletService
     */
    private $mangopayWalletService;

    public function __construct(
        Logger $logger,
        EntityManager $entityManager,
        Translator $translator,
        MangopayApiService $mangopayApiService,
        MangopayWalletService $mangopayWalletService)
    {
        parent::__construct($logger, $entityManager, $translator);
        $this->mangopayApiService = $mangopayApiService;
        $this->mangopayApi = $mangopayApiService->getMangopayApi();
        $this->mangopayWalletService = $mangopayWalletService;
    }

    public function createCouponWalletPayInDirectWeb(int $amountInCent): PayIn
    {
        $couponWalletID = $this->mangopayApiService->getCouponWalletID();
        $couponWallet = $this->mangopayWalletService->getWallet($couponWalletID);
        $payIn = new \MangoPay\PayIn();
        $payIn->CreditedWalletId = $couponWalletID;
        $payIn->CreditedUserId = $couponWallet->Owners[0];
        $payIn->AuthorId = $couponWallet->Owners[0];
        $payIn->Tag = 'Coupon PayIN';
        $payIn->DebitedFunds = new \MangoPay\Money();
        $payIn->DebitedFunds->Currency = 'EUR';
        $payIn->DebitedFunds->Amount = $amountInCent;

        $payIn->Fees = new Money();
        $payIn->Fees->Amount = 0;
        $payIn->Fees->Currency = 'EUR';

        $payIn->ExecutionType = 'WEB';
        $payIn->ExecutionDetails = new \MangoPay\PayInExecutionDetailsWeb();

        if ('mealmatch-stage.frb.io' === $_SERVER['HTTP_HOST']) {
            $payIn->ExecutionDetails->ReturnURL =
                'http'.(isset($_SERVER['HTTPS']) ? 's' : null).'://'.$_SERVER['HTTP_HOST'].
                '/app_stage.php/admin/wallet-manager/show';
        } else {
            $payIn->ExecutionDetails->ReturnURL =
                'http'.(isset($_SERVER['HTTPS']) ? 's' : null).'://'.$_SERVER['HTTP_HOST'].
                '/admin/wallet-manager/show';
        }

        $payIn->ExecutionDetails->Culture = 'EN';
        $payIn->PaymentType = 'DIRECT_DEBIT';
        $payIn->PaymentDetails = new \MangoPay\PayInPaymentDetailsDirectDebit();
        $payIn->PaymentDetails->DirectDebitType = 'SOFORT';

        return $payIn;
    }

    public function createPayInDirectWeb(BaseMealTicket $mealTicket): PayIn
    {
        $this->logger->debug('createPayInDirectWeb--->START');
        $paymentMethod = $mealTicket->getPaymentType();
        $guestUserPaymentProfile = $mealTicket->getGuest()->getPaymentProfile();

        $payIn = new \MangoPay\PayIn();
        $payIn->CreditedWalletId = $guestUserPaymentProfile->getMangopayWalletID();
        $payIn->AuthorId = $guestUserPaymentProfile->getMangopayID();
        $payIn->Tag = $mealTicket->getNumber();
        $payIn->DebitedFunds = new \MangoPay\Money();
        $payIn->DebitedFunds->Currency = $mealTicket->getCurrency();

        // Mealmatch share (fee)
        $mealmatchFeeInCent = $mealTicket->getMmFee() * 100;
        $this->logger->info('PayIn direct web for MT# '.$mealTicket->getNumber().
            ' using fee in cent: '.$mealmatchFeeInCent);
        // netto price
        $mealTicketPriceInCent = $mealTicket->getTotalPriceInCent();
        $mealTicketTaxRate = $this->getTaxRateFromMealType($mealTicket);
        // 10.00€ Meal, 0.19 Tax = 1.90€. Cent value of calc: 1.90 * 100 = 190cent.
        $mealTicketTaxesInCent = (int) ($mealTicket->getTotalPrice() * $mealTicketTaxRate) * 100;
        // Price including taxes to be payed by customer
        $payIn->DebitedFunds->Amount = $mealTicketPriceInCent;

        $currency = $this->getCurrencyFromMealType($mealTicket);
        $payIn->Fees = new \MangoPay\Money();
        $payIn->Fees->Currency = $currency;
        // Mealmatch share (fee)
        $payIn->Fees->Amount = $mealmatchFeeInCent;

        $payIn->ExecutionType = 'WEB';
        $payIn->ExecutionDetails = new \MangoPay\PayInExecutionDetailsWeb();
        $payIn->ExecutionDetails->ReturnURL =
            'http'.(isset($_SERVER['HTTPS']) ? 's' : null).'://'.$_SERVER['HTTP_HOST'].
            '/api/mealticket/'.$mealTicket->getId().'/show';

        // @todo: map some kind of user profile information, or session language
        $payIn->ExecutionDetails->Culture = 'EN';

        if ('SOFORT' === $paymentMethod || 'GIROPAY' === $paymentMethod) {
            $payIn->PaymentType = 'DIRECT_DEBIT';
            $payIn->PaymentDetails = new \MangoPay\PayInPaymentDetailsDirectDebit();
            $payIn->PaymentDetails->DirectDebitType = $paymentMethod;
        } elseif ('CARD' === $paymentMethod) {
            $payIn->PaymentType = 'CARD';
            $payIn->PaymentDetails = new \MangoPay\PayInPaymentDetailsCard();
            $payIn->PaymentDetails->CardType = 'CB_VISA_MASTERCARD';
        }

        $this->logger->info('Created PayIn for MT'.$mealTicket->getNumber().
            'PayIn amount: '.$mealTicketPriceInCent.'including taxes: '.$mealTicketTaxesInCent.'\n'.
            'MangopayPayInObj: '.json_encode($payIn));
        $this->logger->debug('createPayInDirectWeb--->END');

        return $payIn;
    }

    public function doCreatePayInDirectWeb(PayIn $payIn): PayIn
    {
        $this->logger->debug('doCreatePayInDirectWeb--->START');
        $result = $this->mangopayApi->PayIns->Create($payIn);
        $this->logger->debug('doCreatePayInDirectWeb---: '.json_encode($result));
        $this->logger->debug('doCreatePayInDirectWeb--->END');

        return $result;
    }

    /**
     * Return the tax rate by looking at the MealType of the contained BaseMeal.
     *
     * @param BaseMealTicket $mealTicket
     *
     * @return float
     */
    protected function getTaxRateFromMealType(BaseMealTicket $mealTicket): float
    {
        /** @var string short name of implementing class $mealType */
        $mealType = $mealTicket->getBaseMeal()->getMealType();
        switch ($mealType) {
            case 'HomeMeal':
                $mealTicketTaxRate = ApiConstants::DEFAULT_TAX_RATE;
                break;
            case 'ProMeal':
                $mealTicketTaxRate = $mealTicket->getHost()->getRestaurantProfile()->getTaxRate();
                break;
            default:
                die('Unknown MealType: '.$mealType.' emergency stop of execution for security reasons.');
                break;
        }

        return $mealTicketTaxRate;
    }

    /**
     * @param BaseMealTicket $mealTicket
     *
     * @return string
     */
    protected function getCurrencyFromMealType(BaseMealTicket $mealTicket): string
    {
        /** @var string short name of implementing class $mealType */
        $mealType = $mealTicket->getBaseMeal()->getMealType();
        switch ($mealType) {
            case 'HomeMeal':
                $currency = ApiConstants::DEFAULT_CURRENCY;
                break;
            case 'ProMeal':
                $currency = $mealTicket->getHost()->getRestaurantProfile()->getDefaultCurrency();
                break;
            default:
                die('Unknown MealType: '.$mealType.' emergency stop of execution for security reasons.');
                break;
        }

        return $currency;
    }
}
