<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Model;

use Mealmatch\ApiBundle\Entity\Meal\MealEvent;
use Mealmatch\ApiBundle\Exceptions\ServiceDataException;
use Recurr\RecurrenceCollection;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Recurr\Transformer\TextTransformer;

/**
 * The MealEventServiceData contains the MealEvent entity and a dynamically created Rule object for reoccuring events.
 *
 * The MealEventServiceData provides methods to update the MealEvent and the managed Rule object. The rule object is
 * not part of the persisted entity, only the rule definition as a string is stored.
 *
 * @see https://github.com/simshaun/recurr
 */
class MealEventServiceData extends AbstractServiceDataManager
{
    /**
     * Key to hold the MealEvent entity.
     */
    const ENTITY_KEY = 'MealEvent';
    /**
     * Key to hole the RRule generated from the MealEvent entity.
     */
    const RULE_KEY = 'RRULE';

    /**
     * MealEventServiceData constructor. Creates itself using MealEvent as the specification entity.
     */
    public function __construct()
    {
        parent::__construct(self::ENTITY_KEY, new MealEvent());
    }

    /**
     * Sets the internal MealEvent entity, and updates its RRule.
     *
     * @param MealEvent $mealEvent the MealEvent to set
     *
     * @return MealEventServiceData with the updated entity
     */
    public function setMealEvent(MealEvent $mealEvent): self
    {
        $this->setEntity($mealEvent);
        $this->updateInternalRRule($mealEvent->getRrule());

        return $this;
    }

    /**
     * Returns a RecurrenceCollection of DateTimes from the internal RRule.
     *
     * @return RecurrenceCollection
     */
    public function getAvailableDates(): RecurrenceCollection
    {
        $transformer = new ArrayTransformer();

        return $transformer->transform($this->getRRule());
    }

    /**
     * Transforming some recurrence rules into human readable text.
     *
     * @return string a human readable description of the event date reoccurences
     */
    public function getRRuleText(): string
    {
        $textTransformer = new TextTransformer();

        return  $textTransformer->transform($this->getRRule());
    }

    /**
     * Returns the RRule contained.
     *
     * @return Rule
     */
    public function getRRule(): Rule
    {
        return $this->getData(self::RULE_KEY);
    }

    /**
     * Sets the RRule contained by specificing its recurrence RRule.
     *
     * @param string $rRule the string to create the RRule with
     */
    public function setRRule(string $rRule)
    {
        $this->updateInternalRRule($rRule);
    }

    /**
     * Returns the MealEvent entity contained.
     *
     * @throws ServiceDataException if the contained entity is not of the correct type
     *
     * @return MealEvent
     */
    public function getMealEventEntity(): MealEvent
    {
        $entity = parent::getEntity(self::ENTITY_KEY);
        if ($entity instanceof MealEvent) {
            return $entity;
        }
        throw new ServiceDataException('FATAL! MealEvent not of type MealEvent!');
    }

    /**
     * Helper to update the internal RRule object using the rule string specified.
     *
     * @param string $rRule the new RRule to use
     */
    private function updateInternalRRule(string $rRule)
    {
        $mealEvent = $this->getMealEventEntity();

        $eventRRUL = new Rule(
            $rRule,
            $mealEvent->getStartDateTime(),
            $mealEvent->getEndDateTime(),
            $mealEvent->getTimezone()
        );
        $this->setData(self::RULE_KEY, $eventRRUL);
    }
}
