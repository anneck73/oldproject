<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Services;

use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Mealmatch\ApiBundle\Model\ProMealServiceData;
use MMUserBundle\Entity\MMUser;

/**
 * The ProMealService "serves" data to the requesting classes, e.g.: controllers, etc.
 */
interface ProMealServiceInterface extends FinderServiceInterface
{
    public function createFromEntityWithHost(ProMeal $proMeal, MMUser $MMUser): ProMealServiceData;

    public function createFromEntity(ProMeal $proMeal): ProMealServiceData;

    public function restore(int $id): ProMealServiceData;

    public function restoreProMeal(int $id): ProMeal;

    public function getTree(ProMeal $proMeal): array;

    public function isValid(ProMeal $proMeal): bool;
}
