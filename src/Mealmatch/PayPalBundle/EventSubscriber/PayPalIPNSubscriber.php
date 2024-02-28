<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\PayPalBundle\EventSubscriber;

use Doctrine\ORM\EntityManager;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
use Mealmatch\PayPalBundle\Event\PayPalIPN;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribed to Scored Events and persists the score.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class PayPalIPNSubscriber implements EventSubscriberInterface
{
    /**
     * @var Logger the logger to use ...
     */
    private $logger;

    /**
     * The entity manager is used to update the MealTicket.
     *
     * @var EntityManager the entity manager
     */
    private $em;

    /**
     * PayPalIPNSubscriber constructor.
     *
     * @param Logger        $pLogger
     * @param EntityManager $pEm
     */
    public function __construct(Logger $pLogger, EntityManager $pEm)
    {
        $this->em = $pEm;
        $this->logger = $pLogger;
    }

    /**
     * Returns the events subscribed to.
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        // return the subscribed events, their methods and priorities
        return array(
            PayPalIPN::EVENT_NAME => 'afterIPN',
        );
    }

    /**
     * Is dispatched in PayPalManagerService after every PayPalIPN notification.
     *
     *
     * @param PayPalIPN $payPalIPNEvent
     */
    public function afterIPN(PayPalIPN $payPalIPNEvent)
    {
        $this->logger->addInfo('Received: '.$payPalIPNEvent);
        /** @var BaseMealTicket $mealTicket */
        $mealTicket = $payPalIPNEvent->getMealTicket();
        $this->logger->addInfo('Mealticket #'.$mealTicket->getNumber().' status '.$mealTicket->getStatus());

        $guest = $mealTicket->getGuest();
        $basemeal = $mealTicket->getBaseMeal();
        $guests = $basemeal->getGuests();

        if ($guests->contains($guest)) {
            $this->logger->addInfo('Mealticket #'.$mealTicket->getNumber().' user joined basemeal as guest!');
        } else {
            $this->logger->addInfo('Mealticket #'.$mealTicket->getNumber().' user NOT in guestlist!');
        }
    }
}
