<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\PayPalBundle;

final class PayPalConstants
{
    const CONFIG_APP_ID = 'app_id';

    public static function logPrefix($ticketNumber)
    {
        return 'MT('.$ticketNumber.') ';
    }
}
