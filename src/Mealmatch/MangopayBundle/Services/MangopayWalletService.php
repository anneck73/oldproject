<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\MangopayBundle\Services;

use Doctrine\ORM\EntityManager;
use MangoPay\MangoPayApi;
use MangoPay\User;
use MangoPay\Wallet;
use Psr\Log\LoggerInterface as Logger;
use Symfony\Component\Translation\Translator;

class MangopayWalletService extends BaseMangopayService
{
    /** @var MangoPayApi $mangopayApi */
    private $mangopayApi;

    /** @var MangopayApiService $mangopayApiService */
    private $mangopayApiService;

    /**
     * MangopayWalletService constructor.
     *
     * @param Logger             $logger
     * @param EntityManager      $entityManager
     * @param Translator         $translator
     * @param MangopayApiService $mangopayApiService
     */
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
     * @param User $mangopayUser
     *
     * @return Wallet the mangopay wallet
     */
    public function doCreateWallet(User $mangopayUser): Wallet
    {
        $wallet = new \MangoPay\Wallet();
        $wallet->Owners = array($mangopayUser->Id);
        $wallet->Description = 'Mealmatch Wallet';
        $wallet->Currency = 'EUR';

        return $this->mangopayApi->Wallets->Create($wallet);
    }

    public function getWallet(int $mangopayWalletID): Wallet
    {
        return $this->mangopayApi->Wallets->get($mangopayWalletID);
    }

    public function getBalance(int $mangopayWalletID): int
    {
        return $this->mangopayApi->Wallets->Get($mangopayWalletID)->Balance;
    }

    /**
     * Returns the "Coupon" Wallet.
     *
     * @return Wallet
     */
    public function getCouponWallet(): Wallet
    {
        $couponWalletID = $this->mangopayApiService->getCouponWalletID();

        return $this->getWallet($couponWalletID);
    }
}
