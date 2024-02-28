<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\WorkflowBundle\Event\Subscriber;

use Mealmatch\ApiBundle\Entity\Meal\BaseMeal;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Workflow\Event\GuardEvent;

class MealCreateGuard implements EventSubscriberInterface
{
    /**
     * The FlashBag.
     *
     * @var FlashBagInterface
     */
    private $flashBag;

    public function __construct(FlashBagInterface $flashBag)
    {
        $this->flashBag = $flashBag;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            'workflow.base_meal.guard.create_meals' => 'onCreateMeals',
        );
    }

    public function onCreateMeals(GuardEvent $guardEvent)
    {
        /** @var BaseMeal $meal */
        $meal = $guardEvent->getSubject();

        if ('-' === $meal->getAddress()->getLocationString()) {
            $guardEvent->setBlocked(true);
        }
    }
}
