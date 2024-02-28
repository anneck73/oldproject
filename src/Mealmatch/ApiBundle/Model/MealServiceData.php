<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Model;

use Mealmatch\ApiBundle\Entity\Meal\HomeMeal;
use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Mealmatch\ApiBundle\Exceptions\ServiceDataException;
use Mealmatch\ApiBundle\Services\MealService;

/**
 * @todo: Finish PHPDoc!
 * MealServiceData is used by ProMealService AND HomeMealService, it has a variable BASE Entity.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class MealServiceData extends AbstractServiceDataManager
{
    private $specMap = array(
        MealService::HOME_SPEC => 'Mealmatch\\ApiBundle\\Entity\\Meal\\HomeMeal',
        MealService::BUSINESS_SPEC => 'Mealmatch\\ApiBundle\\Entity\\Meal\\ProMeal',
    );

    public function __toString(): string
    {
        return basename(__CLASS__).' Type: '.$this->getSpecification().' isValid: '.$this->isValid();
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param HomeMeal $homeMealEntity
     * @param string   $myArgument     with a *description* of this argument, these may also
     *                                 span multiple lines
     *
     * @return $this
     */
    public function setHomeMeal(HomeMeal $homeMealEntity)
    {
        $this->setEntity($homeMealEntity);

        return $this;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param ProMeal $proMealEntity
     * @param string  $myArgument    with a *description* of this argument, these may also
     *                               span multiple lines
     *
     * @return $this
     */
    public function setProMeal(ProMeal $proMealEntity)
    {
        $this->setEntity($proMealEntity);

        return $this;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @throws ServiceDataException
     *
     * @return ProMeal returns the ProMeal entity contained in this ServiceData
     */
    public function getProMeal(): ProMeal
    {
        foreach ($this->getEntities() as $entity) {
            if ($entity instanceof ProMeal) {
                return $entity;
            }
        }

        throw new ServiceDataException(
            'Could not find entity of class ProMeal!'
        );
    }

    public function getHomeMeal(): HomeMeal
    {
        foreach ($this->getEntities() as $entity) {
            if ($entity instanceof HomeMeal) {
                return $entity;
            }
        }

        throw new ServiceDataException(
            'Could not find entity of class HomeMeal!'
        );
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $reqNotMeetTrans the reason for the error
     */
    public function addError(string $reqNotMeetTrans): void
    {
        $current = $this->getErrors();
        $current->add($reqNotMeetTrans);
        $this->data->set(ServiceDataSpecification::ERRORS_KEY, $current);
    }
}
