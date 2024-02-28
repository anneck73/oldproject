<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Model;

use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Mealmatch\ApiBundle\Exceptions\ServiceDataException;
use Mealmatch\ApiBundle\Exceptions\ServiceDataValidationException;

/**
 * @todo: Finish PHPDoc!
 * @todo: Finish validate() method!
 * A summary informing the user what the class ProMealServiceData does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class ProMealServiceData extends MealServiceData
{
    public function __construct()
    {
        parent::__construct('ProMeal', new ProMeal());
    }

    /**
     * Validates the internal service data.
     *
     * @throws ServiceDataValidationException if validation fails
     * @throws ServiceDataException
     */
    public function validate(): void
    {
        $this->setValidity(false);

        // start to validate ...

        /** @var ProMeal $entity */
        $entity = $this->getEntity($this->getSpecification());

        // We require a host to be set!!
        if (null === $entity->getHost()) {
            $this->addError('Validation failed: no Host!');
            throw new ServiceDataValidationException('ProMeal has no HOST!');
        }

        // We need to have at least "one"
        if ($entity->getMealAddresses()->count() < (int) 1) {
            $this->addError('Validation failed: no Address!');
            throw new ServiceDataValidationException('ProMeal has no MealAddress!');
        }

        // We need to have at least "one"
        if ($entity->getMealOffers()->count() < (int) 1) {
            $this->addError('Validation failed: no MealOffers!');
            throw new ServiceDataValidationException('ProMeal has no MealOffers!');
        }

        $this->setValidity(true);
    }
}
