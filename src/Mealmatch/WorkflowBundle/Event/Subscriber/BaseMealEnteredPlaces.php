<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\WorkflowBundle\Event\Subscriber;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use FOS\MessageBundle\Composer\Composer;
use FOS\MessageBundle\Sender\Sender;
use Mealmatch\ApiBundle\Entity\Meal\BaseMeal;
use Mealmatch\ApiBundle\Services\MealService;
use Monolog\Logger;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Workflow\Event\Event;
use Twig\Error\Error;

/**
 * This Subscriber listens to every workflow.base_meal.entered.* specification.
 * For every PLACE the SUBJECT is PERSISTED and the EntityManager FLUSHED!
 * Additionally the FOS:Message services are used to send a FOS:Message to the user executing the transition.
 */
class BaseMealEnteredPlaces implements EventSubscriberInterface
{
    /**
     * Used to create meals as specified.
     *
     * @var MealService
     */
    private $mealService;

    /**
     * The logger.
     *
     * @var Logger
     */
    private $logger;

    /**
     * FOS:Message composer service.
     *
     * @var Composer
     */
    private $composer;

    /**
     * The TokenStorage to obtain the current user.
     *
     * @var TokenStorage
     */
    private $storage;

    /**
     * The FOS:Message sender service.
     *
     * @var Sender
     */
    private $sender;

    /**
     * The entiy manager to get user details.
     *
     * @var EntityManager
     */
    private $entityManager;

    /** @var TwigEngine $twigEngine */
    private $twigEngine;

    /**
     * BaseMealEnteredPlaces constructor.
     *
     * @param Logger       $logger
     * @param MealService  $mealService
     * @param Composer     $composer
     * @param Sender       $sender
     * @param TokenStorage $storage
     */
    public function __construct(
        Logger $logger,
        EntityManager $entityManager,
        MealService $mealService,
        Composer $composer,
        Sender $sender,
        TokenStorage $storage,
        TwigEngine $twigEngine
    ) {
        $this->mealService = $mealService;
        $this->logger = $logger;
        $this->composer = $composer;
        $this->storage = $storage;
        $this->sender = $sender;
        $this->entityManager = $entityManager;
        $this->twigEngine = $twigEngine;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        // workflow.[workflow name].entered.[place name]
        return array(
            'workflow.base_meal.entered.CREATED' => array(
                array('safeSubject', 1),
            ),
            'workflow.base_meal.entered.READY' => array(
                array('safeSubject', 1),
            ),
            'workflow.base_meal.entered.RUNNING' => array(
                array('safeSubject', 1),
            ),
            'workflow.base_meal.entered.STOPPED' => array(
                array('safeSubject', 1),
            ),
            'workflow.base_meal.entered.CLOSED' => 'safeSubject',
            'workflow.base_meal.entered.DELETED' => 'safeSubject',
            'workflow.base_meal.entered.FINISHED' => array(
                array('safeSubject', 1),
                array('createUserMessage', 2),
            ),
        );
    }

    /**
     * Persist and flushes the subject of this event.
     *
     * @param Event $event the transition event
     */
    public function safeSubject(Event $event): void
    {
        /** @var BaseMeal $meal */
        $meal = $event->getSubject();
        // persist and flush this meal ...
        try {
            $this->mealService->safe($meal);
        } catch (OptimisticLockException $optimisticLockException) {
            $this->logger->addError(
                'Could not safe mail id: '.$meal->getId()
                .' error: '.$optimisticLockException->getMessage()
            );
        }
        // be informative ...
        $this->logger->alert('Safed mealID: '.$meal->getId().' '.$meal->getStatus());
    }

    /**
     * Creates and sends the FOS:Message to the user executing this transition.
     *
     * @param Event $event the event of the transition
     */
    public function createUserMessage(Event $event): void
    {
        /** @var BaseMeal $meal */
        $meal = $event->getSubject();

        // System Message to Host ...
        $messageBody = $this->getMessageToHostBodyTemplate($meal);
        $this->sendSystemMessageToHost($meal, $messageBody);

        // System Message(s) to Guest(s)
        $messageBodyToGuests = $this->getMessageToGuestsBodyTemplate($meal);
        $this->sendSystemMessageToGuests($meal, $messageBodyToGuests);
    }

    /**
     * Private helper to put a "System Message" into the inbox of the HOST of the BaseMeal.
     * Note: This Method should fail silently if something goes wrong!
     *
     * @param BaseMeal $meal        the meal the notification is about
     * @param string   $messageBody the message body to use
     */
    private function sendSystemMessageToHost(BaseMeal $meal, string $messageBody): void
    {
        $mealTitle = $meal->getTitle();
        $mealID = $meal->getId();
        $status = $meal->getStatus();
        $recipient = $meal->getHost();
        $sender = $this->entityManager->getRepository('MMUserBundle:MMUser')->findOneBy(
            array('username' => 'SYSTEM')
        );

        // If setup of LIVE/STAGE/DEV/PROD/TEST is missing a SYSTEM USER!!!
        if (null === $sender) {
            $this->logger->alert('Failed to send FOS:Message! SYSTEM USER NOT FOUND! ');

            return;
        }

        try {
            $message = $this->composer->newThread()
                ->addRecipient($recipient)
                ->setSubject("Das Meal $mealTitle (#$mealID) ist nun im Status: $status !")
                ->setSender($sender)
                ->setBody($messageBody)
                ->getMessage();

            $this->sender->send($message);
            $this->logger->info('Send FOS:Message.');
        } catch (\Exception $exception) {
            $this->logger->alert('Failed to send FOS:Message!');
        }
    }

    /**
     * Private helper to put a "System Message" into the inbox of all GUEST's of the BaseMeal.
     * Note: This Method should fail silently if something goes wrong!
     *
     * @param BaseMeal $meal        the meal the notification is about
     * @param string   $messageBody the message body to use
     */
    private function sendSystemMessageToGuests(BaseMeal $meal, string $messageBody): void
    {
        $mealTitle = $meal->getTitle();
        $mealID = $meal->getId();
        $status = $meal->getStatus();
        $recipients = $meal->getGuests();
        $sender = $this->entityManager->getRepository('MMUserBundle:MMUser')->findOneBy(
            array('username' => 'SYSTEM')
        );

        // If setup of LIVE/STAGE/DEV/PROD/TEST is missing a SYSTEM USER!!!
        if (null === $sender) {
            $this->logger->alert('Failed to send FOS:Message! SYSTEM USER NOT FOUND! ');

            return;
        }

        try {
            $message = $this->composer->newThread()
                ->addRecipients($recipients)
                ->setSubject("Das Meal $mealTitle (#$mealID) ist nun im Status: $status !")
                ->setSender($sender)
                ->setBody($messageBody)
                ->getMessage();

            $this->sender->send($message);
            $this->logger->info('Send FOS:Message.');
        } catch (\Exception $exception) {
            $this->logger->alert('Failed to send FOS:Message!');
        }
    }

    /**
     * Private Helper to return the body for the message to all guests of a BaseMeal.
     *
     * @param BaseMeal $meal
     *
     * @return string the body to be used in the system message
     */
    private function getMessageToGuestsBodyTemplate(BaseMeal $meal)
    {
        $templateName = '@Api/SystemMessages/BaseMeal/'.strtoupper($meal->getStatus()).'-ToGuests.twig';

        return $this->renderBody($meal, $templateName);
    }

    /**
     * Private Helper to return the body for the message to the host of a BaseMeal.
     *
     * @param BaseMeal $meal
     *
     * @return string the body to be used in the system message
     */
    private function getMessageToHostBodyTemplate(BaseMeal $meal)
    {
        $templateName = '@Api/SystemMessages/BaseMeal/'.strtoupper($meal->getStatus()).'-ToHost.twig';

        return $this->renderBody($meal, $templateName);
    }

    /**
     * Private helper to "render" a template using data from a BaseMeal.
     *
     * @param BaseMeal $meal
     * @param string   $templateName
     *
     * @return string containing an error message or the result of the render process
     */
    private function renderBody(BaseMeal $meal, $templateName): string
    {
        // fail fast ...
        $templateExists = $this->twigEngine->exists($templateName);
        if (!$templateExists) {
            return 'Failed to find TWIG template ('.$templateName.') for System-Message!';
        }
        // template exists, let's try to read it ...
        try {
            return $this->twigEngine->render($templateName, array('meal' => $meal));
        } catch (Error $error) {
            $this->logger->error('Failed to read TWIG template for System-Message! '.$error->getMessage());

            return 'Error in ('.$templateName.'): '.$error->getMessage();
        }
    }
}
