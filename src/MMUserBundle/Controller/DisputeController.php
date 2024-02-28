<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Controller;

use MangoPay\Libraries\IStorageStrategy;
use MangoPay\MangoPayApi;
use Mealmatch\ApiBundle\Controller\ApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/dispute")
 */
class DisputeController extends ApiController
{
    protected $logger;
    protected $swift;
    private $mangoPayApi;

    public function __construct(array $mangopayCredentials, $logger, $swift)
    {
        $this->mangoPayApi = new MangoPayApi();
        $this->logger = $logger;
        $this->swift = $swift;
        if ($mangopayCredentials['live']) {
            $this->mangoPayApi->Config->ClientId = $mangopayCredentials['production']['client_id'];
            $this->mangoPayApi->Config->ClientPassword = $mangopayCredentials['production']['client_password'];
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
     * dispute Event triggered by hooks.
     *
     * @Route("/disputehooks",name="disputeUpdate",)
     *
     * @param Request $request the HTTP Request to process
     * @Method("GET")
     */
    public function disputeHooks(Request $request)
    {
        try {
            $eventType = $request->get('EventType');
            $resourceId = $request->get('RessourceId');
            $response = new Response();
            $response->setStatusCode(200);

            if (\MangoPay\EventType::DisputeCreated === $eventType) {
                $dispute = $this->mangoPayApi->Disputes->Get($resourceId);
                $subject = 'Dispute created';
                $data = 'Hello Mealmatch,<br>There is a dispute created by an end user towards you. The dispute Id is '.$resourceId.
                        ' and the dispute type is '.$dispute->DisputeType.'.';
                $this->sendEmail($data, $subject);
                $this->logger->alert('DISPUTE_CREATED hooks');
            } elseif (\MangoPay\EventType::DisputeActionRequired === $eventType) {
                $subject = 'Dispute Action Required';
                $data = 'Hello Mealmatch,<br>Dispute(Id:'.$resourceId.') created towards you is CONTESTABLE.<br> So, further actions required from your side. 
                         Either contest or close the dispute.';
                $this->sendEmail($data, $subject);
                $this->logger->alert('DISPUTE_ACTION_REQUIRED hooks:');
            } elseif (\MangoPay\EventType::DisputeDocumentSucceeded === $eventType) {
                $DisputeDocument = $this->mangoPayApi->DisputeDocuments->Get($resourceId);
                $subject = 'Dispute Document Succeeded';
                $data = 'Hello Mealmatch,<br>The dispute document submitted by you get succeeded. The document Id is '.$resourceId.
                        ' and the respective dispute Id is'.$DisputeDocument->DisputeId.'.';
                $this->sendEmail($data, $subject);
                $this->logger->alert('DISPUTE_DOCUMENT_SUCCEEDED hooks:');
            } elseif (\MangoPay\EventType::DisputeDocumentFailed === $eventType) {
                $DisputeDocument = $this->mangoPayApi->DisputeDocuments->Get($resourceId);
                $subject = 'Dispute Document Failed';
                $data = 'Hello Mealmatch,<br>The dispute document submitted by you for the dispute '.$DisputeDocument->DisputeId.' get failed.<br> 
                         The document Id is '.$resourceId.' and the reason for rejection is '.$DisputeDocument->RefusedReasonMessage.'.';
                $this->sendEmail($data, $subject);
                $this->logger->alert('DISPUTE_DOCUMENT_FAILED hooks:');
            } elseif (\MangoPay\EventType::DisputeSentToBank === $eventType) {
                $subject = 'Dispute Sent to Bank';
                $data = 'Hello Mealmatch,<br>Documents submitted for the dispute(Id:'.$resourceId.') get forwarded to the bank after verified by Mangopay.';
                $this->sendEmail($data, $subject);
                $this->logger->alert('DISPUTE_SENT_TO_BANK hooks:');
            } elseif (\MangoPay\EventType::DisputeFurtherActionRequired === $eventType) {
                $dispute = $this->mangoPayApi->Disputes->Get($resourceId);
                $subject = 'Dispute Further Action Required';
                $data = 'Hello Mealmatch,<br>For the dispute with Id:'.$resourceId.', the respective bank request you to submit further evidences.<br> 
                         Reason for that request is '.$dispute->StatusMessage.'.';
                $this->sendEmail($data, $subject);
                $this->logger->alert('DISPUTE_FURTHER_ACTION_REQUIRED hooks:');
            } elseif (\MangoPay\EventType::DisputeClosed === $eventType) {
                $dispute = $this->mangoPayApi->Disputes->Get($resourceId);
                $subject = 'Dispute Closed';
                $data = 'Hello Mealmatch,<br>The dispute(Id:'.$resourceId.') was closed and the result message is'.$dispute->ResultMessage.'.';
                $this->sendEmail($data, $subject);
                $this->logger->alert('DISPUTE_CLOSED hooks:');
            }

            $this->logger->alert('Dispute hooks: 200 response');

            return $response;
        } catch (\Exception $ex) {
            $this->logger->alert('Dispute hooks Exception: '.$ex->getMessage().
                'Exception code: '.$ex->getCode());

            return $response;
        }
    }

    /**
     * Actually sends the email using SWIFT.
     *
     * @param string $data    Body of the message
     * @param string $subject Subject of the message
     *
     * @return int
     */
    private function sendEmail($data, $subject)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom('contact.form@mealmatch.de')
            ->setTo('mailer@mealmatch.de')
            ->setBody(
                $this->renderView(
                    '@MMUser/Disputes/disputes.html.twig',
                    array(
                        'message' => $data,
                    )
                ),
                'text/html'
            )
        ;

        return $this->swift->send($message);
    }
}
