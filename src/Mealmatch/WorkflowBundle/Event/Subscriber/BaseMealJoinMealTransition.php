<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\WorkflowBundle\Event\Subscriber;

use Mealmatch\ApiBundle\Entity\Meal\BaseMeal;
use Mealmatch\ApiBundle\Model\MealServiceData;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

/**
 * @todo: Implement this transition!!!
 * @todo: Finish PHPDoc!
 * This class only LOGS its action, there is no implementation or integration with mealmatch logic yet!
 */
class BaseMealJoinMealTransition extends AbstractBaseMealSubscriber implements EventSubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            'workflow.base_meal.transition.join_meal' => array('joinRequest'),
        );
    }

    /**
     * @todo: Implement method!
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param Event $event
     */
    public function joinRequest(Event $event)
    {
        /** @var BaseMeal $meal */
        $meal = $event->getSubject();
        $result = new MealServiceData('HomeMeal', $meal);
        $user = $this->storage->getToken()->getUser();
        $this->logger->alert('JOIN_MEAL!');
        // $this->mealService->getHomeMealService()->createJoinRequest($meal, $user);
    }
}
