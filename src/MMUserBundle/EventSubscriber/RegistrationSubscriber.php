<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\EventSubscriber;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Mealmatch\ApiBundle\MealMatch\UserManager;
use MMUserBundle\Entity\MMUser;
use Monolog\Logger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @todo: As of today we have not found a good use for this listener!
 *
 * Only loggind the registration event ... we are listening!
 */
final class RegistrationSubscriber implements EventSubscriberInterface
{
    /** @var UserManager $um */
    private $um;

    /** @var Logger $logger */
    private $logger;

    /**
     * RegistrationListener constructor.
     */
    public function __construct(UserManager $pUM, Logger $pLog)
    {
        $this->um = $pUM;
        $this->logger = $pLog;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_SUCCESS => 'onRegistrationSuccess',
        );
    }

    public function onRegistrationSuccess(FormEvent $event)
    {
        /** @var $user MMUser */
        $user = $event->getForm()->getData();
        $this->logger->addDebug('onRegistrationSuccess: '.$user->getUsername());
    }
}
