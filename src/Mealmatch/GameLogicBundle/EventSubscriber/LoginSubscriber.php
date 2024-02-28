<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\GameLogicBundle\EventSubscriber;

use Mealmatch\GameLogicBundle\Core\Score;
use Mealmatch\GameLogicBundle\Core\UserScore;
use Mealmatch\GameLogicBundle\Event\Scored;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher as EventDispatcher;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * The LoginSubscriber class subsribes to SecurityEvents::INTERACTIVE_LOGIN and dispatches a new
 * Scored::USER event with a ScoreType "Counter" value 1.
 *
 * In other words, you get +1 counter for each login.
 */
class LoginSubscriber implements EventSubscriberInterface
{
    /**
     * @var Logger the logger to use ...
     */
    private $logger;

    /**
     * The EventDispatcher is used to trigger new events ...
     *
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * LoginSubscriber constructor.
     *
     * @param Logger          $pLogger     the logger to use
     * @param EventDispatcher $pDispatcher the event dispatcher to use
     */
    public function __construct(Logger $pLogger, $pDispatcher)
    {
        $this->logger = $pLogger;
        $this->dispatcher = $pDispatcher;
        $this->logger->addDebug(
            sprintf('Created %s', __CLASS__)
        );
    }

    /**
     * Returns an Array of the events we are subscribed to:
     *  SecurityEvents::INTERACTIVE_LOGIN
     *  AuthenticationEvents::AUTHENTICATION_SUCCESS.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return array(
            SecurityEvents::INTERACTIVE_LOGIN => 'onLogin',
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onAuth',
        );
    }

    /**
     * As of today this method only logges the event to debug.
     *
     * @param AuthenticationEvent $pEvent the AuthEvent to process
     */
    public function onAuth(AuthenticationEvent $pEvent)
    {
        $this->logger->addDebug(
            sprintf('OnAuth %s', $pEvent->getAuthenticationToken()->getUsername())
        );
    }

    /**
     * On User Login a new Game.user.scored event is triggered.
     *
     * @param InteractiveLoginEvent $pEvent the LoginEvent to process
     */
    public function onLogin(InteractiveLoginEvent $pEvent)
    {
        $this->logger->addDebug(
            sprintf('OnLogin %s', $pEvent->getAuthenticationToken()->getUsername())
        );
        // Game Score ... UserScore for Login of type "Counter"
        $gameUser = $pEvent->getAuthenticationToken()->getUser();
        $userScore = new UserScore($gameUser, 1, 'login', Score::COUNTER_TYPE);
        // Game Scorec Event with Score ...
        $scoredEvent = new Scored($userScore);
        // Dispatch Scored:USER event ...
        $this->dispatcher->dispatch(Scored::USER, $scoredEvent);
    }
}
