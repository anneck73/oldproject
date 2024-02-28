<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\MangopayBundle;

/**
 * Storage strategy implementation for tests.
 */
class MockStorageStrategy implements \MangoPay\Libraries\IStorageStrategy
{
    private static $_oAuthToken = null;

    /**
     * Gets the current authorization token.
     *
     * @return \MangoPay\Libraries\OAuthToken currently stored token instance or null
     */
    public function Get()
    {
        return self::$_oAuthToken;
    }

    /**
     * Stores authorization token passed as an argument.
     *
     * @param \MangoPay\Libraries\OAuthToken $token token instance to be stored
     */
    public function Store($token)
    {
        self::$_oAuthToken = $token;
    }
}
