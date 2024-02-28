<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\MealMatch;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mealmatch\ApiBundle\Entity\Meal\BaseMeal;

/**
 * Helper class to work with BaseMeals and Collections and filtering.
 *
 * All "filter" methods should be public static, use a collection and return a collection.
 */
class CollectionHelper
{
    /**
     * Filter by "city" finds only matching BaseMeals.
     *
     * @param Collection $baseMeals a collection of BaseMeals
     * @param string     $city      the city to search for
     *
     * @return Collection the collection of Meals matching the city specified
     */
    public static function filterBaseMealsByCity(Collection $baseMeals, string $city): Collection
    {
        return $baseMeals->filter(
            function (BaseMeal $meal) use ($city) {
                return $city === $meal->getAddress()->getCity();
            }
        );
    }

    /**
     * Filter by "dateTime" finds all BaseMeals where the startDateTime is >= the $dateTime specified.
     *
     * @param Collection $baseMeals a collection of BaseMeals
     * @param DateTime   $dateTime  the dateTime to compare
     *
     * @return Collection the collection of Meals matching the filter
     */
    public static function filterBaseMealsEqualOrAfterStart(Collection $baseMeals, DateTime $dateTime): Collection
    {
        return $baseMeals->filter(
            function (BaseMeal $meal) use ($dateTime) {
                return $meal->getStartDateTime() >= $dateTime;
            }
        );
    }

    /**
     * Filter by "dateTime" finds all BaseMeals where the startDateTime is < the $dateTime specified.
     *
     * @param Collection $baseMeals a collection of BaseMeals
     * @param DateTime   $dateTime  the dateTime to compare
     *
     * @return Collection the collection of Meals matching the filter
     */
    public static function filterBaseMealsBeforeStart(Collection $baseMeals, DateTime $dateTime): Collection
    {
        return $baseMeals->filter(
            function (BaseMeal $meal) use ($dateTime) {
                return $meal->getStartDateTime() < $dateTime;
            }
        );
    }

    /**
     * Filter by "dateTime" finds all BaseMeals where the startDateTime is > the $dateTime specified.
     *
     * @param Collection $baseMeals a collection of BaseMeals
     * @param DateTime   $dateTime  the dateTime to compare
     *
     * @return Collection the collection of Meals matching the filter
     */
    public static function filterBaseMealsAfterStart(Collection $baseMeals, DateTime $dateTime): Collection
    {
        return $baseMeals->filter(
            function (BaseMeal $meal) use ($dateTime) {
                return $meal->getStartDateTime() > $dateTime;
            }
        );
    }

    /**
     * Sort the provided ArrayCollection of BaseMeals by their startDateTime ASC.
     *
     * @param ArrayCollection $baseMeals
     *
     * @return ArrayCollection sorted by startDateTime ASC
     */
    public static function sortByStartDate(ArrayCollection $baseMeals): ArrayCollection
    {
        $resultIt = $baseMeals->getIterator();
        $resultIt->uasort(
            function (BaseMeal $a, BaseMeal $b) {
                return ($a->getStartDateTime() < $b->getStartDateTime()) ? -1 : 1;
            }
        );

        return new ArrayCollection(iterator_to_array($resultIt));
    }

    /** @todo: WiP */
    public function sortByStatus(ArrayCollection $baseMeals): ArrayCollection
    {
        $resultIt = $baseMeals->getIterator();
        $statusOrder = array('Aktive' => 1, 'Stopped' => 2);

        $resultIt->uasort(
            function (BaseMeal $a, BaseMeal $b) {
                return ($a->getStatus() < $b->getStartDateTime()) ? -1 : 1;
            }
        );

        return new ArrayCollection(iterator_to_array($resultIt));
    }
}
