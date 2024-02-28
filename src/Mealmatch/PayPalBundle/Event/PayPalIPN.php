<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\PayPalBundle\Event;

use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
use Symfony\Component\EventDispatcher\Event;

class PayPalIPN extends Event
{
    const EVENT_NAME = 'PayPalIPN';

    private $mealTicket;

    public function __construct(BaseMealTicket $mealTicket)
    {
        $this->mealTicket = $mealTicket;
    }

    public function __toString()
    {
        return 'PayPalIPN EVENT with MealTicket: '.$this->mealTicket->getJson();
    }

    /**
     * @return BaseMealTicket
     */
    public function getMealTicket(): BaseMealTicket
    {
        return $this->mealTicket;
    }
}
