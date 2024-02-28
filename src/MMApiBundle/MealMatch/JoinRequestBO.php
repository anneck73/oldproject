<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMApiBundle\MealMatch;

use MMApiBundle\Entity\JoinRequest;

class JoinRequestBO
{
    public static $STATUS_CREATED = 'CREATED';
    public static $STATUS_ACCEPTED = 'ACCEPTED';
    public static $STATUS_DENIED = 'DENIED';
    public static $STATUS_PAYED = 'PAYED';

    /** @var JoinRequest */
    private $data;

    public function __construct(JoinRequest $pData)
    {
        $this->data = $pData;
    }

    public function getStatus(): string
    {
        $returnValue = static::$STATUS_CREATED;

        if ($this->data->isPayed()) {
            $returnValue = static::$STATUS_PAYED;
        } else {
            if ($this->data->isAccepted()) {
                $returnValue = static::$STATUS_ACCEPTED;
            } else {
                if ($this->data->isDenied()) {
                    $returnValue = static::$STATUS_DENIED;
                }
            }
        }

        return $returnValue;
    }
}
