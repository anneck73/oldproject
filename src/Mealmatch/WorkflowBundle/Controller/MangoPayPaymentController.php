<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\WorkflowBundle\Controller;

use FOS\MessageBundle\Composer\Composer;
use FOS\MessageBundle\Sender\Sender;
use MangoPay\Libraries\IStorageStrategy;
use MangoPay\MangoPayApi;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\WorkflowBundle\SystemMessage;
use Monolog\Logger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("api/workflow")
 */
class MangoPayPaymentController extends Controller
{
    protected $logger;
    protected $composer;
    protected $sender;
    protected $twig;
    private $mangoPayApi;

    public function __construct(array $mangopayCredentials,
        Logger $logger,
        Composer $composer,
        Sender $sender,
        TwigEngine $twig
    ) {
        $this->mangoPayApi = new MangoPayApi();
        $this->logger = $logger;
        $this->composer = $composer;
        $this->sender = $sender;
        $this->twig = $twig;

        if ($mangopayCredentials['live']) {
            $this->mangoPayApi->Config->ClientId = $mangopayCredentials['production']['client_id'];
            $this->mangoPayApi->Config->ClientPassword = $mangopayCredentials['production']['client_password'];
            //$this->mangoPayApi->Config->BaseUrl = 'https://api.mangopay.com';
            $this->logger->alert('Mangopay LIVE!');
            $this->logger->alert('ClientID: '.$mangopayCredentials['production']['client_id']);
            $this->logger->alert('ApiKey: '.$mangopayCredentials['production']['client_password']);
        } else {
            $this->mangoPayApi->Config->ClientId = $mangopayCredentials['sandbox']['client_id'];
            $this->mangoPayApi->Config->ClientPassword = $mangopayCredentials['sandbox']['client_password'];
            $this->logger->alert('Mangopay SANDBOX!');
            $this->logger->alert('ClientID: '.$mangopayCredentials['sandbox']['client_id']);
            $this->logger->alert('ApiKey: '.$mangopayCredentials['sandbox']['client_password']);
        }
        $InMemoryStorage = (new class() implements IStorageStrategy {
            private static $_oAuthToken = null;

            /**
             * Gets the current authorization token.
             *
             * @return \MangoPay\Libraries\OAuthToken currently stored token instance or null
             */
            public function Get()
            {
                return self::$_oAuthToken;
            }

            /**
             * Stores authorization token passed as an argument.
             *
             * @param \MangoPay\Libraries\OAuthToken $token token instance to be stored
             */
            public function Store($token)
            {
                self::$_oAuthToken = $token;
            }
        });
        $this->mangoPayApi->OAuthTokenManager->RegisterCustomStorageStrategy($InMemoryStorage);
    }

    /**
     * @param Request $request
     *
     * @Route("/paymenterror/paymentnotification", name="notification_error", methods={"GET","POST"})
     */
    public function paymentNotificationErrorAction(Request $request)
    {
        $this->logger->addDebug('paymentNotificationErrorAction() received Response: '.$request->getContent());

        return $this->render('@MealmatchWorkflow/PaymentNotification.html.twig', array(
            'state' => 'paymenterror', ));
    }

    /**
     * @param Request $request
     *
     * @Route("/{id}/paymentnotification", name="notification")
     *
     * @Method("GET")
     */
    public function paymentNotificationAction(Request $request, string $id): RedirectResponse
    {
        $this->logger->addAlert('paymentNotificationAction('.$id.') received Response: '.$request->getContent());

        $PayInId = $request->get('transactionId');
        $PayIn = $this->mangoPayApi->PayIns->Get($PayInId);

        $status = $PayIn->Status;

        if ('SUCCEEDED' === $status) {
            $this->logger->addAlert('paymentNotificationAction('.$id.') PayIn->Status: '.$status);
        }
        die('Doh!');
    }

//    /**
//     * call payout from code base.
//     *
//     * @param int $transactionId
//     */
//    public function doPayoutAction(int $transactionId)
//    {
//        $this->logger->addAlert("doPayoutAction($transactionId)");
//        $entityManager = $this->get('doctrine.orm.default_entity_manager');
//        /** @var MealTicketTransaction $mealTicketTransaction */
//        $mealTicketTransaction = $entityManager->getRepository('ApiBundle:Meal\MealTicketTransaction')->findOneByResourceId($transactionId);
//        if (null === $mealTicketTransaction) {
//            $this->logger->alert('MealTicketTransaction not exist for the id '.$transactionId);
//
//            return 2;
//        }
//        /** @var BaseMealTicket $mealTicket */
//        $mealTicket = $entityManager->getRepository('ApiBundle:Meal\BaseMealTicket')->find($mealTicketTransaction->getMealTicketId());
//        if (null === $mealTicket) {
//            $this->logger->alert('MealTicket not exist');
//
//            return 2;
//        }
//        // Mealticket transaction changes...
//        $mealTicketTransaction->setPaymentStatus(ApiConstants::TRANSACTION_SUCCEEDED);
//        $entityManager->persist($mealTicketTransaction);
//        // Mealticket changes ...
//        $mealTicket->setStatus(ApiConstants::MEAL_TICKET_STATUS_PAYED);
//        $mealTicket->setResourceId($transactionId);
//        $mealTicket->addTransaction($mealTicketTransaction);
//        $entityManager->persist($mealTicket);
//        // flush all changes to entities
//        $entityManager->flush();
//
//        $this->get('workflow.meal_ticket')->apply($mealTicket, 'payout');
//
//        $checkMealPayOutTransactionCreated = $entityManager->getRepository('ApiBundle:Meal\MealTicketTransaction')->findOneByPayOutSourceResourceId($transactionId);
//
//        if (null !== $checkMealPayOutTransactionCreated) {
//            $this->logger->alert('Payout Successfully created');
//
//            return 1;
//        }
//        $this->logger->alert('Payout not created. Something went wrong');
//
//        return 0;
//    }

    /**
     * Events triggered by hooks.
     *
     * @Route("/mangopayhooks",name="doPayout", )
     *
     * @param Request $request the HTTP Request to process
     * @Method("GET")
     */
    public function mangoPayHooksApi(Request $request)
    {
        $entityManager = $this->get('doctrine.orm.default_entity_manager');
        $eventType = $request->get('EventType');
        $resourceId = $request->get('RessourceId');
        $notifiedDate = $request->get('Date');
        $admin = $entityManager->getRepository('MMUserBundle:MMUser')->findOneByEmail('mailer@mealmatch.de');

        $this->logger->alert('Hook Api => '.
            ' EventType: '.$eventType.
            ' RessourceId: '.$resourceId.
            ' Date: '.$notifiedDate
        );
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(200);

        if (null === $eventType || null === $resourceId) {
            $this->logger->alert('Hook Api : Either EventType or RessourceId is null');

            return $response;
        }
        //Handle Payin Normal Succeeded event
        if (\MangoPay\EventType::PayinNormalSucceeded === $eventType) {
            $mealTicketTransaction = $entityManager->getRepository('ApiBundle:Meal\MealTicketTransaction')->findOneByResourceId($resourceId);
            $mealPayOutTransaction = $entityManager->getRepository('ApiBundle:Meal\MealTicketTransaction')->findOneByPayOutSourceResourceId($resourceId);
            $user = $entityManager->getRepository('MMUserBundle:MMUser')->findOneById($mealTicketTransaction->getUserID());

            if (null === $mealTicketTransaction) {
                $this->logger->alert('Hook Api PAYIN_NORMAL_SUCCEEDED: 
                                           There is no MealTicketTransaction entry for the resource id'.$resourceId);

                return $response;
            }
            $mealTicket = $entityManager->getRepository('ApiBundle:Meal\BaseMealTicket')->find($mealTicketTransaction->getMealTicketId());
            $subject = 'Payment succeeded';
            $message = 'Hello, payment to '.$mealTicket->getTitel().' was succeeded.';
            $this->sendSystemMessage($subject, $message, $user, $admin);
            $mealTicketTransaction->setPaymentStatus(ApiConstants::TRANSACTION_STATUS_SUCCEEDED);
            $entityManager->persist($mealTicketTransaction);
            $entityManager->flush();
            $this->logger->alert('Hook Api PAYIN_NORMAL_SUCCEEDED : finished');

            return $response;
        }
        //Handle Payin Normal Failed event
        elseif (\MangoPay\EventType::PayinNormalFailed === $eventType) {
            $mealTicketTransaction = $entityManager->getRepository('ApiBundle:Meal\MealTicketTransaction')->findOneByResourceId($resourceId);
            if (null === $mealTicketTransaction) {
                $this->logger->alert('Hook Api PAYIN_NORMAL_FAILED : There is no MealTicketTransaction exist for the id '.$resourceId);

                return $response;
            }
            $user = $entityManager->getRepository('MMUserBundle:MMUser')->findOneById($mealTicketTransaction->getUserID());
            $subject = 'Payment failed';
            $message = 'Hello, payment to the restaurant get failed.';
            $this->sendSystemMessage($subject, $message, $user, $admin);
            $mealTicketTransaction->setPaymentStatus(ApiConstants::TRANSACTION_STATUS_FAILED);
            $entityManager->persist($mealTicketTransaction);
            $entityManager->flush();
            $this->logger->alert('Hook Api PAYIN_NORMAL_FAILED : finished');

            return $response;
        }
        //Handle Payout Normal created event
        elseif (\MangoPay\EventType::PayoutNormalCreated === $eventType) {
            $mealPayOutTransaction = $entityManager->getRepository('ApiBundle:Meal\MealTicketTransaction')->findOneByResourceId($resourceId);
            if (null === $mealPayOutTransaction) {
                $this->logger->alert('Hook Api PAYOUT_NORMAL_CREATED : There is no MealTicketTransaction(payout) exist for the id '.$resourceId);

                return $response;
            }
            $user = $entityManager->getRepository('MMUserBundle:MMUser')->findOneById($mealPayOutTransaction->getUserID());
            $subject = 'Payout created';
            $message = 'Hello, payout to your bank account get created.';
            $this->sendSystemMessage($subject, $message, $user, $admin);
            $mealPayOutTransaction->setPaymentStatus(ApiConstants::TRANSACTION_STATUS_CREATED);
            $entityManager->persist($mealPayOutTransaction);
            $entityManager->flush();
            $this->logger->alert('Hook Api PAYOUT_NORMAL_CREATED : finished');

            return $response;
        }
        //Handle Payout Normal succeeded event
        elseif (\MangoPay\EventType::PayoutNormalSucceeded === $eventType) {
            $mealPayOutTransaction = $entityManager->getRepository('ApiBundle:Meal\MealTicketTransaction')->findOneByResourceId($resourceId);
            if (null === $mealPayOutTransaction) {
                $this->logger->alert('Hook Api PAYOUT_NORMAL_SUCCEEDED : There is no MealTicketTransaction(payout) exist for the id '.$resourceId);

                return $response;
            }
            $user = $entityManager->getRepository('MMUserBundle:MMUser')->findOneById($mealPayOutTransaction->getUserID());
            $subject = 'Payout succeeded';
            $message = 'Hello, payout to your bank account get succeeded.';
            $this->sendSystemMessage($subject, $message, $user, $admin);
            $mealPayOutTransaction->setPaymentStatus(ApiConstants::TRANSACTION_STATUS_SUCCEEDED);
            $entityManager->persist($mealPayOutTransaction);
            $entityManager->flush();
            $this->logger->alert('Hook Api PAYOUT_NORMAL_SUCCEEDED : finished');

            return $response;
        }
        //Handle Payout Normal failed event
        elseif (\MangoPay\EventType::PayoutNormalFailed === $eventType) {
            $mealPayOutTransaction = $entityManager->getRepository('ApiBundle:Meal\MealTicketTransaction')->findOneByResourceId($resourceId);
            if (null === $mealPayOutTransaction) {
                $this->logger->alert('Hook Api PAYOUT_NORMAL_FAILED : There is no MealTicketTransaction(payout) exist for the id '.$resourceId);

                return $response;
            }
            $user = $entityManager->getRepository('MMUserBundle:MMUser')->findOneById($mealPayOutTransaction->getUserID());

            $subject = 'Payout failed';
            $message = 'Hello, payout to your bank account get failed';
            $this->sendSystemMessage($subject, $message, $user, $admin);
            $mealPayOutTransaction->setPaymentStatus(ApiConstants::TRANSACTION_STATUS_FAILED);
            $entityManager->persist($mealPayOutTransaction);
            $entityManager->flush();

            $this->logger->alert('Hook Api PAYOUT_NORMAL_FAILED : finished');

            return $response;
        }

        return $response;
    }

    protected function sendSystemMessage($subject, $message, $recipient, $sender)
    {
        $sysMsgToUser = (new SystemMessage())
            ->setSubject($subject)
            ->setMessage($message)
            ->setRecipient($recipient)
            ->setSender($sender);
        $body = $this->twig->render(
            '@MealmatchWorkflow/PayPalSystemMessage.partial.html.twig',
            array(
                'message' => $sysMsgToUser,
            )
        );
        $message = $this->composer->newThread()
            ->addRecipient($recipient)
            ->setSubject($subject)
            ->setSender($sender)
            ->setBody($body)
            ->getMessage();

        $this->sender->send($message);
    }
}
