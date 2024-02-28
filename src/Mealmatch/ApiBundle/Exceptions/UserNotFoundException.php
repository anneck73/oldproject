<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Exceptions;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Indicates that the user specified was not found.
 *
 * @todo: add functionality to set cause e.g. ->user specified by $cause
 */
class UserNotFoundException extends UsernameNotFoundException
{
    /**
     * Returns the JSON encoded message data of this exception.
     *
     * @return string json encoded message data
     */
    public function __toString(): string
    {
        return json_encode($this->getMessageData());
    }
}
