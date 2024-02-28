<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\MangopayBundle\Services;

use MangoPay\Libraries\IStorageStrategy;
use Psr\Log\LoggerInterface as Logger;
use Symfony\Component\HttpFoundation\Session\Session;

class MangopayStorage implements IStorageStrategy
{
    public static $KEY = 'MANGOPAY_OAUTH_TOKEN';
    /** @var Session $session */
    private $session;
    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Session $session, Logger $logger)
    {
        $this->session = $session;
        $this->logger = $logger;
    }

    /**
     * Gets the current authorization token.
     *
     * @return \MangoPay\Libraries\OAuthToken currently stored token instance or null
     */
    public function Get()
    {
        $this->logger->debug('MangopaySessionStorage get('.self::$KEY.'):'.json_encode($this->session->get(self::$KEY)));

        return $this->session->get(self::$KEY);
    }

    /**
     * Stores authorization token passed as an argument.
     *
     * @param \MangoPay\Libraries\OAuthToken $token token instance to be stored
     */
    public function Store($token)
    {
        $this->logger->debug('MangopaySessionStorage set('.self::$KEY.'):'.json_encode($token));
        $this->session->set(self::$KEY, $token);
    }
}
