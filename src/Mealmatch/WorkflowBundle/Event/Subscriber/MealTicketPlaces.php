<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\WorkflowBundle\Event\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class MealTicketPlaces extends AbstractMealTicketSubscriber implements EventSubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            'workflow.meal_ticket.entered.CREATED' => array(
                array('safeSubject', 1),
            ),
            'workflow.meal_ticket.entered.PROCESSING_PAYMENT' => array(
                array('safeSubject', 1),
                array('sendSystemMessages', 2),
            ),
            'workflow.meal_ticket.entered.PAYMENT_ERROR' => array(
                array('safeSubject', 1),
                array('sendSystemMessages', 2),
            ),
            'workflow.meal_ticket.entered.PREPARE_PAYMENT' => array(
                array('safeSubject', 1),
                array('sendSystemMessages', 2),
            ),
            'workflow.meal_ticket.entered.PAYED' => array(
                array('safeSubject', 1),
                array('sendSystemMessages', 2),
            ),
            'workflow.meal_ticket.entered.CANCELLED' => array(
                array('safeSubject', 1),
                array('sendSystemMessages', 2),
            ),
            'workflow.meal_ticket.entered.USED' => array(
                array('safeSubject', 1),
                array('sendSystemMessages', 2),
            ),
        );
    }
}
