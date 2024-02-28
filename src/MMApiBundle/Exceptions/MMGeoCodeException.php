<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMApiBundle\Exceptions;

use Exception;

class MMGeoCodeException extends MMException
{
    /**
     * The location address string causing the Exception.
     *
     * @var string
     */
    private $locationAddress;

    public function __construct($locAddress, $message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->locationAddress = $locAddress;
    }

    /**
     * @return string
     */
    public function getLocationAddress(): string
    {
        return $this->locationAddress;
    }
}
