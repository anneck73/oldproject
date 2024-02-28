<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Model;

use Mealmatch\ApiBundle\Entity\Meal\HomeMeal;
use Mealmatch\ApiBundle\Entity\Meal\MealAddress;
use Mealmatch\ApiBundle\Exceptions\ServiceDataValidationException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class HomeMealServiceData extends MealServiceData
{
    public function __construct()
    {
        parent::__construct('HomeMeal', new HomeMeal());
    }

    /**
     * Validates the internal ServiceData.
     *
     * @throws ServiceDataValidationException if validation fails
     */
    public function validate()
    {
        $this->setValidity(false);

        /** @var HomeMeal $homeMeal */
        $homeMeal = $this->getEntity('HomeMeal');
        // MealPart Requirements: min:1 max:3
        $countMealParts = $homeMeal->getMealParts()->count();

        if ($countMealParts > 3 || $countMealParts < 1) {
            $this->addError('HomeMeal\'s should have between 1 and max. 3 MealPart\'s.');
            // throw new ServiceDataValidationException('HomeMeal\'s should have between 1 and max. 3 MealPart\'s.');
        }

        $lang = new ExpressionLanguage();

        /** @var MealAddress $mealAddr */
        $mealAddr = $homeMeal->getAddress();
        if ($lang->evaluate(
            'mealAddr.getCity() !== null',
            array('mealAddr' => $mealAddr)
        )) {
            $this->setValidity(true);
        } else {
            $this->addError('MealAddress is not valid!');
            $this->setValidity(false);
        }

        // Set validation ...
        $this->setValidity(true);
    }
}
