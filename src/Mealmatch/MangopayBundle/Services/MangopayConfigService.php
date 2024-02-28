<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\MangopayBundle\Services;

use Psr\Log\LoggerInterface as Logger;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * The MangopayConfigService class is used to configure the Mangopay API using provided credentials.
 * The credentials are extracted from the container parameter configuration using the ParameterBagInterface.
 * Use this service via DI if for access to credentials or other configurations.
 */
class MangopayConfigService
{
    // Makes this service container aware to access parameters.
    use ContainerAwareTrait;

    /** @var array $credentials */
    private $credentials;

    /** @var Logger $logger */
    private $logger;

    /** @var array $parameters */
    private $couponWalletID;

    /**
     * MangopayConfigService constructor.
     *
     * @param Logger $logger
     */
    public function __construct(Logger $logger, ContainerInterface $container)
    {
        // The logger
        $this->logger = $logger;
        $this->container = $container;

        // The config service requires mangopay_credentials
        try {
            $this->credentials = $this->container->getParameter('mangopay_credentials');
        } catch (ParameterNotFoundException $notFoundException) {
            $this->logger->error('Mangopay credentials not found!');
        }
        // The config service requires a coupon wallet id
        try {
            $this->couponWalletID = $this->container->getParameter('mangopay_coupon_wallet_id');
        } catch (ParameterNotFoundException $notFoundException) {
            $this->logger->error('Mangopay coupon wallet id not found!');
        }
    }

    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * @param mixed $setting
     *
     * @return string
     */
    public function getCouponWalletID($setting): string
    {
        return $this->couponWalletID[$setting];
    }
}
