<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\WorkflowBundle\Event\Subscriber;

use Doctrine\ORM\EntityManager;
use FOS\MessageBundle\Composer\Composer;
use FOS\MessageBundle\Model\MessageInterface;
use FOS\MessageBundle\Sender\Sender;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
use Mealmatch\ApiBundle\Services\MealTicketService;
use Mealmatch\PayPalBundle\Entity\PayPalPaymentToken;
use Mealmatch\WorkflowBundle\SystemMessage;
use Monolog\Logger;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Workflow\Event\Event;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class AbstractBaseMealSubscriber does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class AbstractMealTicketSubscriber
{
    /**
     * @todo: Finish PHPDoc!
     *
     * @var MealTicketService
     */
    protected $mealTicketService;

    /**
     * The logger.
     *
     * @var Logger
     */
    protected $logger;

    /**
     * FOS:Message composer service.
     *
     * @var Composer
     */
    protected $composer;

    /**
     * The TokenStorage to obtain the current user.
     *
     * @var TokenStorage
     */
    protected $storage;

    /**
     * The FOS:Message sender service.
     *
     * @var Sender
     */
    protected $sender;

    /**
     * The entiy manager to get user details.
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var TwigEngine
     */
    protected $twigEngine;

    /**
     * AbstractConstructor for all MealTicket related subscribers.
     *
     * @param Logger            $logger
     * @param EntityManager     $entityManager
     * @param MealTicketService $mealTicketService
     * @param Composer          $composer
     * @param Sender            $sender
     * @param TokenStorage      $storage
     * @param TwigEngine        $twigEngine
     */
    public function __construct(
        Logger $logger,
        EntityManager $entityManager,
        MealTicketService $mealTicketService,
        Composer $composer,
        Sender $sender,
        TokenStorage $storage,
        TwigEngine $twigEngine
    ) {
        $this->mealTicketService = $mealTicketService;
        $this->logger = $logger;
        $this->composer = $composer;
        $this->storage = $storage;
        $this->sender = $sender;
        $this->entityManager = $entityManager;
        $this->twigEngine = $twigEngine;
    }

    /**
     * Creates and sends the FOS:Message to the user executing this transition.
     *
     * @param Event $event the event of the transition
     */
    public function sendSystemMessages(Event $event): void
    {
        /** @var BaseMealTicket $mealTicket */
        $mealTicket = $event->getSubject();
        $host = $mealTicket->getHost();
        $guest = $mealTicket->getGuest();
        $ticketNumber = $mealTicket->getNumber();
        $ticketID = $mealTicket->getId();
        $status = $mealTicket->getStatus();

        /** @var PayPalPaymentToken $lastPaymentToken */
        /*   $lastPaymentToken = $mealTicket->getPaymentTokens()->last();
           $tokenStatus = $lastPaymentToken->getTokenStatus();*/
        /*MangoPay PayIn Status*/
        $tokenStatus = $mealTicket->getPayInStatus();
        $sysMsgToHost = (new SystemMessage())
            ->setSubject("Das MealTicket $ticketNumber (#$ticketID) ist nun im Status: $status / $tokenStatus !")
            ->setMessage("Hallo $host, ...")
            ->setRecipient($mealTicket->getHost())
            ->setSender($guest);

        $sysMsgToGuest = (new SystemMessage())
            ->setSubject("Das MealTicket $ticketNumber (#$ticketID) ist nun im Status: $status / $tokenStatus !")
            ->setMessage("Hallo $guest, ...")
            ->setRecipient($mealTicket->getGuest())
            ->setSender($host);

        try {
            $message2Host = $this->composeMessage($sysMsgToHost);
            $this->sender->send($message2Host);

            $message2Guest = $this->composeMessage($sysMsgToGuest);
            $this->sender->send($message2Guest);

            $this->logger->info('FOS:Messages send.');
        } catch (\Exception $exception) {
            $this->logger->alert('FOS:Messages failed to send!');
        }
    }

    /**
     * Persist and flushes the subject of this event.
     *
     * @param Event $event the transition event
     */
    public function safeSubject(Event $event): void
    {
        /** @var BaseMealTicket $mealTicket */
        $mealTicket = $event->getSubject();
        $this->entityManager->persist($mealTicket);
        $this->entityManager->flush();
        // be informative ...
        $this->logger->alert('Safed TicketID: '.$mealTicket->getId().' '.$mealTicket->getStatus());
    }

    /**
     * Helper to compose a valid FOS:Message from a SystemMessage using TWIG.
     *
     * @param SystemMessage $systemMessage
     *
     * @return MessageInterface
     */
    protected function composeMessage(
        SystemMessage $systemMessage
    ): MessageInterface {
        $body = $this->twigEngine->render(
            '@MealmatchWorkflow/PayPalSystemMessage.partial.html.twig',
            array(
                'message' => $systemMessage,
            )
        );

        $message = $this->composer->newThread()
            ->addRecipient($systemMessage->getRecipient())
            ->setSubject($systemMessage->getSubject())
            ->setSender($systemMessage->getSender())
            ->setBody($body)
            ->getMessage();

        return $message;
    }
}
