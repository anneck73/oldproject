<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\CalendarBundle\Services;

use Mealmatch\ApiBundle\Exceptions\ServiceDataException;
use Mealmatch\ApiBundle\Model\MealServiceData;
use Mealmatch\CalendarBundle\Model\CalendarServiceData;

class CalendarService
{
    public function create(MealServiceData $mealServiceData): CalendarServiceData
    {
        if ($mealServiceData->isValid()) {
            $csd = new CalendarServiceData();
            $csd->addBaseMeal($mealServiceData);

            return $csd;
        }
        throw new ServiceDataException('MealServiceData invalid!');
    }
}
