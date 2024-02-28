<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Repository\Meal;

use Gedmo\Sortable\Entity\Repository\SortableRepository;

/**
 * The entity repository class for MealPart.
 * Extending SortableRepository to benefit from its query classes.
 */
class MealPartRepository extends SortableRepository
{
}
