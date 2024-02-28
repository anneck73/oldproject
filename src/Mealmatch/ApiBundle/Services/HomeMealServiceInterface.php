<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Services;

use Mealmatch\ApiBundle\Entity\Meal\HomeMeal;
use Mealmatch\ApiBundle\Model\HomeMealServiceData;
use MMUserBundle\Entity\MMUser;

/**
 * The HomeMealService "serves" data to the requesting classes, e.g.: controllers, etc.
 */
interface HomeMealServiceInterface extends FinderServiceInterface
{
    /**
     * Creates a new HomeMeal with the specified MMUser as the host (createdBy).
     *
     * @param HomeMeal $homeMeal the HomeMeal to use as input for creation
     * @param MMUser   $MMUser   the MMUser to become the host of the meal
     *
     * @return HomeMealServiceData contains the result of the operation
     */
    public function createFromEntityWithHost(HomeMeal $homeMeal, MMUser $MMUser): HomeMealServiceData;

    /**
     * Simply creates a new HomeMeal without applying any validation!
     *
     * @param HomeMeal $homeMeal the HomeMeal to use as input for creation
     *
     * @return HomeMealServiceData containes the result of the operation
     */
    public function createFromEntity(HomeMeal $homeMeal): HomeMealServiceData;

    public function restore(int $id): HomeMealServiceData;

    public function restoreHomeMeal(int $id): HomeMeal;

    public function getTree(HomeMeal $homeMeal): array;

    public function isValid(HomeMeal $homeMeal): bool;
}
