<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\MangopayBundle\Controller;

use FOS\MessageBundle\Model\MessageInterface;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Controller\ApiController;
use Mealmatch\WorkflowBundle\SystemMessage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class KYCHooksController extends ApiController
{
    /**
     * KYC Event triggered by hooks.
     *
     * @Route("/kychooks",name="updateKyc", )
     *
     * @param Request $request the HTTP Request to process
     * @Method("GET")
     *
     * @return Response
     */
    public function kycHooks(Request $request)
    {
        try {
            $entityManager = $this->get('doctrine.orm.default_entity_manager');
            $eventType = $request->get('EventType');
            $resourceId = $request->get('RessourceId');
            $kyc = $entityManager->getRepository('MMUserBundle:MMUserKYCProfile')->findOneByKycId($resourceId);
            $sender = $this->get('fos_message.sender');
            $userId = $kyc->getUserID();
            $user = $entityManager->getRepository('MMUserBundle:MMUser')->findOneById($userId);
            $admin = $entityManager->getRepository('MMUserBundle:MMUser')->findOneByEmail('mailer@mealmatch.de');
            $mangopayUserId = $kyc->getMangopayUserID();
            $KycDocument = $this->mangoPayApi->Users->GetKycDocument($mangopayUserId, $resourceId);
            $refusedReason = $KycDocument->RefusedReasonMessage;
            $kycDocType = $KycDocument->Type;
            $roles = $user->getRoles();

            if ('KYC_SUCCEEDED' === $eventType) {
                $kyc->setStatus(ApiConstants::KYC_SUCCEEDED);
                if (\in_array('ROLE_HOME_USER', $roles, true)) {
                    $user->setOverallKycStatus('Approved');
                } elseif (\in_array('ROLE_RESTAURANT_USER', $roles, true)) {
                    $userKycPofile = $entityManager->getRepository('MMUserBundle:MMUserKYCProfile')->findAllByMangopayUserID($mangopayUserId);
                    if (4 === \count($userKycPofile)) {
                        $i = 0;
                        foreach ($userKycPofile as $userKyc) {
                            if (ApiConstants::KYC_SUCCEEDED === $userKyc->getStatus()) {
                                ++$i;
                            } else {
                                break;
                            }
                        }
                        if (4 === $i) {
                            $user->setOverallKycStatus('Approved');
                        }
                    }
                }
                $sysMsgToUser = (new SystemMessage())
                    ->setSubject('Kyc document validated by Mangopay')
                    ->setMessage("Hello, Your KYC document submitted for $kycDocType get successfully validated by Mangopay.")
                    ->setRecipient($user)
                    ->setSender($admin);
                $message2User = $this->composeMessage($sysMsgToUser);
                $sender->send($message2User);
                $this->logger->alert('KYC hooks: KYC_SUCCEEDED');
            } elseif ('KYC_FAILED' === $eventType) {
                //if kyc failed, then notify the user by a system message, then delete the entry from database
                $sysMsgToUser = (new SystemMessage())
                    ->setSubject('Kyc document refused by Mangopay')
                    ->setMessage("Hello, Your KYC document submitted for $kycDocType get refused by Mangopay due to the following reason. ".$refusedReason.'')
                    ->setRecipient($user)
                    ->setSender($admin);
                $message2User = $this->composeMessage($sysMsgToUser);
                $sender->send($message2User);
                $deleteRow = $entityManager->getRepository('MMUserBundle:MMUserKYCProfile')->deleteByKycId($resourceId);
                $this->logger->alert('KYC hooks: KYC_FAILED');
            }
            $entityManager->persist($kyc);
            $entityManager->flush();
            $response = new Response();
            $response->setStatusCode(200);
            $this->logger->alert('KYC hooks: 200 response');

            return $response;
        } catch (\Exception $ex) {
            $response = new Response();
            $response->setStatusCode(500);
            $this->logger->alert('KYC hooks: 500 response');

            return $response;
        }
    }

    /**
     * Helper to compose a valid FOS:Message from a SystemMessage using TWIG.
     *
     * @param SystemMessage $systemMessage
     *
     * @throws \Twig\Error\Error
     *
     * @return MessageInterface
     */
    protected function composeMessage(
        SystemMessage $systemMessage
    ): MessageInterface {
        $twigEngine = $this->get('templating');
        $body = $twigEngine->render(
            '@MealmatchWorkflow/PayPalSystemMessage.partial.html.twig',
            array(
                'message' => $systemMessage,
            )
        );
        $composer = $this->get('fos_message.composer');
        $message = $composer->newThread()
            ->addRecipient($systemMessage->getRecipient())
            ->setSubject($systemMessage->getSubject())
            ->setSender($systemMessage->getSender())
            ->setBody($body)
            ->getMessage();

        return $message;
    }
}
