<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\MangopayBundle\Services;

use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\ORM\EntityManager;
use MangoPay\Libraries\IStorageStrategy;
use MangoPay\MangoPayApi;
use Psr\Log\LoggerInterface as Logger;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\Translator;

/**
 * The MangopayApiService class creates a valid MangopayApi for other services to work with.
 */
class MangopayApiService extends BaseMangopayService
{
    use ContainerAwareTrait;

    private $mangopayApi;
    /**
     * @var MangopayConfigService
     */
    private $configService;

    /**
     * @var Session
     */
    private $session;

    public function __construct(
        Logger $logger,
        EntityManager $entityManager,
        Translator $translator,
        MangopayConfigService $configService,
        Session $session)
    {
        parent::__construct($logger, $entityManager, $translator);
        $this->mangopayApi = $this->createMangopayApi($configService, $session);
        $this->configService = $configService;
        $this->session = $session;
    }

    public function getMangopayUserService(): ?MangopayUserService
    {
        $mangopayUserService = $this->container->get('MangopayUserService');
        if ($mangopayUserService instanceof MangopayUserService) {
            return $mangopayUserService;
        }

        return null;
    }

    /**
     * @return MangoPayApi
     */
    public function getMangopayApi(): MangoPayApi
    {
        return $this->mangopayApi;
    }

    public function getCouponWalletID(): string
    {
        // setting is sandbox or live
        $setting = 'sandbox'; // default
        // Get environment, only works on fortrabbit
        $appName = getenv('APP_NAME');
        if ('mealmatch' === $appName) {
            // this is live
            $setting = 'live';
        }

        return $this->configService->getCouponWalletID($setting);
    }

    private function createMangopayApi(
        MangopayConfigService $configService,
        Session $session): MangoPayApi
    {
        // API Initialization only once please...
        if (null !== $this->mangopayApi) {
            return $this->mangopayApi;
        }

        $mangopayCredentials = $configService->getCredentials();
        // determine which credentials to use (sandbox|production)
        $setting = $this->getEnvSetting();
        $appName = getenv('APP_NAME');
        $mangopayApi = new MangoPayApi();
        // Only in production we change the BaseUrl, default is api.dashboard.mangopay.com
        if ('production' === $setting) {
            $mangopayApi->Config->BaseUrl = 'https://api.mangopay.com';
        }
        $mangopayApi->Config->ClientId = $mangopayCredentials[$setting]['client_id'];
        $mangopayApi->Config->ClientPassword = $mangopayCredentials[$setting]['client_password'];

        $this->logger->notice("APP_NAME($appName): MangopayApi[$setting]:ClientID:"
            .$mangopayCredentials[$setting]['client_id']);

        // Create the Storage for Mangopay API
        $inSessionStorage = new MangopayStorage($session, $this->logger);
        // Register the Storage Service
        $mangopayApi->OAuthTokenManager->RegisterCustomStorageStrategy($inSessionStorage);
        // Finished, set the internal value
        $this->mangopayApi = $mangopayApi;
        // and return it.
        return $mangopayApi;
    }

    /**
     * @return string
     */
    private function getEnvSetting(): string
    {
        // setting is sandbox or production
        $setting = 'sandbox'; // default
        // Get environment, only works on fortrabbit
        $appName = getenv('APP_NAME');
        if (false !== $appName && 'mealmatch-stage' === $appName) {
            // STAGE has to be SANDBOX too!
            $setting = 'sandbox';
        }
        if (false !== $appName && 'mealmatch-dev' === $appName) {
            // DEV is always SANDBOX
            $setting = 'sandbox';
        }
        if (false !== $appName && 'mealmatch' === $appName) {
            // LIVE is always PRODCUTION
            $setting = 'production';
        }

        return $setting;
    }

    /**
     * @param MemcachedCache $cache
     *
     * @return IStorageStrategy
     */
    private function createMemcachedStorage(MemcachedCache $cache, string $tokenID)
    {
        $InMemcachedStorage = new class($cache, $tokenID) implements IStorageStrategy {
            /** @var null $id */
            private $id = null;
            /** @var MemcachedCache $storage null */
            private $cachedStorage = null;

            public function __construct($storage, $tokenID)
            {
                $this->cachedStorage = $storage;
                $this->id = 'MPOAUTH/'.$tokenID;
            }

            /**
             * Gets the current authorization token.
             *
             * @return \MangoPay\Libraries\OAuthToken currently stored token instance or null
             */
            public function Get()
            {
                if ($this->cachedStorage->contains($this->id)) {
                    return $this->cachedStorage->fetch($this->id);
                }

                return null;
            }

            /**
             * Stores authorization token passed as an argument.
             *
             * @param \MangoPay\Libraries\OAuthToken $token token instance to be stored
             */
            public function Store($token)
            {
                // Using the current session id as the id to store the mangopay oauth token.
                $this->cachedStorage->save($this->id, $data = array('token' => $token), $lifeTime = 360);
            }
        };

        return $InMemcachedStorage;
    }
}
