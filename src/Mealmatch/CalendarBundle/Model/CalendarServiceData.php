<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\CalendarBundle\Model;

use Mealmatch\ApiBundle\Model\AbstractServiceDataManager;
use Mealmatch\ApiBundle\Model\MealServiceData;
use Mealmatch\CalendarBundle\Entity\Calendar\Calendar;

class CalendarServiceData extends AbstractServiceDataManager
{
    /**
     * CalendarServiceData constructor.
     */
    public function __construct()
    {
        parent::__construct('CalendarService', new Calendar());
    }

    public function addBaseMeal(MealServiceData $mealServiceData): self
    {
        $this->addEntity('BaseMeal', $mealServiceData->getBaseMeal());

        return $this;
    }
}
