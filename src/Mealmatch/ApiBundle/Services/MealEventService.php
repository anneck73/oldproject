<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Mealmatch\ApiBundle\Entity\Meal\HomeMeal;
use Mealmatch\ApiBundle\Entity\Meal\MealEvent;
use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Mealmatch\ApiBundle\Model\MealEventServiceData;
use Monolog\Logger;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class MealEventService
{
    /**
     * @todo: Finish PHPDoc!
     *
     * @var Logger
     */
    private $logger;
    /**
     * @todo: Finish PHPDoc!
     *
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @todo: Finish PHPDoc!
     *
     * @var Translator
     */
    private $translator;

    /**
     * MealEventService constructor.
     *
     * @param Logger        $logger
     * @param EntityManager $entityManager
     * @param Translator    $translator
     */
    public function __construct(
        Logger $logger,
        EntityManager $entityManager,
        Translator $translator
    ) {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param ProMeal $proMeal
     * @param string  $myArgument with a *description* of this argument, these may also
     *                            span multiple lines
     *
     * @return ArrayCollection
     */
    public function getAvailableDatesForProMeal(ProMeal $proMeal): ArrayCollection
    {
        $availableDates = new ArrayCollection();
        /** @var MealEvent $mealEvent */
        foreach ($proMeal->getMealEvents() as $mealEvent) {
            if ($mealEvent->isReoccuring()) {
                $events = $this->getAvailableDates($mealEvent);
                foreach ($events as $v) {
                    $availableDates->add($v);
                }
            } else {
                $availableDates->add(
                    array(
                        'start' => $mealEvent->getStartDateTime(),
                        'end' => $mealEvent->getEndDateTime(),
                        'idx' => 0,
                        'eventID' => $mealEvent->getId(),
                    )
                );
            }
        }

        return $availableDates;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param HomeMeal $homeMeal
     *
     * @return ArrayCollection a collection of eventArrays
     */
    public function getAvailableDatesForHomeMeal(HomeMeal $homeMeal): ArrayCollection
    {
        $availableDates = new ArrayCollection();
        /** @var MealEvent $mealEvent */
        foreach ($homeMeal->getMealEvents() as $mealEvent) {
            if ($mealEvent->isReoccuring()) {
                $events = $this->getAvailableDates($mealEvent);
                foreach ($events as $v) {
                    $availableDates->add($v);
                }
            } else {
                $availableDates->add(
                    array(
                        'start' => $mealEvent->getStartDateTime(),
                        'end' => $mealEvent->getEndDateTime(),
                        'idx' => 0,
                        'eventID' => $mealEvent->getId(),
                    )
                );
            }
        }

        return $availableDates;
    }

    /**
     * Returns a textual and human readable representation of the MealEvent "event time(s)".
     *
     * @param MealEvent $mealEvent
     *
     * @return string the human readable event text
     */
    public function getText(MealEvent $mealEvent): string
    {
        if ($mealEvent->isReoccuring()) {
            $serviceData = new MealEventServiceData();
            $serviceData->setMealEvent($mealEvent);

            return $serviceData->getRRuleText();
        }

        return sprintf(
            'Von %s bis %s',
            $mealEvent->getStartDateTime(),
            $mealEvent->getEndDateTime()
        );
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param MealEvent $mealEvent the MealEvent used to calculate the available dates
     *
     * @return Collection of DateTime objects matching the available dates for the mealEvent
     */
    public function getAvailableDates(MealEvent $mealEvent): Collection
    {
        $rRule = new Rule(
            $mealEvent->getRrule(),
            $mealEvent->getStartDateTime(),
            $mealEvent->getEndDateTime(),
            $mealEvent->getTimezone()
        );

        $transformer = new ArrayTransformer();
        $recCollection = $transformer->transform($rRule);

        $availableDates = array();
        foreach ($recCollection as $recurrence) {
            $availableDates[] = array(
                'start' => $recurrence->getStart(),
                'end' => $recurrence->getEnd(),
                'idx' => $recurrence->getIndex(),
                'eventID' => $mealEvent->getId(),
            );
        }

        return new ArrayCollection($availableDates);
    }
}
