<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Exceptions;

use Exception;

class GeoCodeException extends MMException
{
    /**
     * The location address string causing the Exception.
     *
     * @var string
     */
    private $locationString;

    public function __construct($locString, $message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->locationString = $locString;
    }

    /**
     * @return string
     */
    public function getLocationString(): string
    {
        return $this->locationString;
    }
}
