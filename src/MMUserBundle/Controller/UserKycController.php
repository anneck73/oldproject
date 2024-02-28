<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\MessageBundle\Model\MessageInterface as MessageInterfaceAlias;
use MangoPay\Libraries\IStorageStrategy;
use MangoPay\MangoPayApi;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Controller\ApiController;
use Mealmatch\WorkflowBundle\SystemMessage;
use MMUserBundle\Entity\MMUser;
use MMUserBundle\Entity\MMUserKYCProfile;
use MMUserBundle\Form\KycDocumentTypeAA;
use MMUserBundle\Form\KycDocumentTypeID;
use MMUserBundle\Form\KycDocumentTypeRP;
use MMUserBundle\Form\KycDocumentTypeSD;
use Recurr\Exception;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Error\Error as TwigError;

/**
 * @todo: Finish PHPDoc!
 *
 * @Route("profile/kyc")
 */
class UserKycController extends ApiController
{
    protected $logger;
    private $mangoPayApi;

    public function __construct(array $mangopayCredentials, $logger)
    {
        $this->mangoPayApi = new MangoPayApi();
        $this->logger = $logger;

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
        $InMemoryStorage = new class() implements IStorageStrategy {
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
        };
        $this->mangoPayApi->OAuthTokenManager->RegisterCustomStorageStrategy($InMemoryStorage);
    }

    /**
     * Shows the kyc details to the user.
     *
     * @param Request $request
     * @Route("/show", name="userprofile_kyc_show")
     *
     * @return Response
     */
    public function showManager(Request $request)
    {
        $this->logger->info('Render KYC about page to user');

        return $this->render('@MMUser/KYC/about_kyc.html.twig', array());
    }

    /**
     * Create and display KYC document forms for host.
     *
     * @param Request $request
     * @Route("/upload", name="kyc_show")
     *
     * @return Response
     */
    public function showKyc(Request $request)
    {
        $formTabOne = $this->createTabForm(KycDocumentTypeID::class, 1);
        $formTabTwo = $this->createTabForm(KycDocumentTypeRP::class, 2);
        $formTabThree = $this->createTabForm(KycDocumentTypeAA::class, 3);
        $formTabFour = $this->createTabForm(KycDocumentTypeSD::class, 4);
        $errorMsg = $request->get('errorMessage');
        $selectedTab = $this->getSelectedTab($request);

        if (null !== $errorMsg) {
            switch ($selectedTab) {
                case '1':
                    foreach ($errorMsg as $error) {
                        $formTabOne->addError(new FormError($error));
                    }
                    break;
                case '2':
                    foreach ($errorMsg as $error) {
                        $formTabTwo->addError(new FormError($error));
                    }
                    break;
                case '3':
                    foreach ($errorMsg as $error) {
                        $formTabThree->addError(new FormError($error));
                    }
                    break;
                case '4':
                    foreach ($errorMsg as $error) {
                        $formTabFour->addError(new FormError($error));
                    }
                    break;
            }
        }
        $kycStatus = $this->checkKycStatusForHost();
        $renderViewData = new ArrayCollection(array(
                'selectedTab' => $selectedTab,
                'formTabOne' => $formTabOne->createView(),
                'formTabTwo' => $formTabTwo->createView(),
                'formTabThree' => $formTabThree->createView(),
                'formTabFour' => $formTabFour->createView(),
                'kyc_status' => $kycStatus,
            )
        );

        return $this->render('@MMUser/KYC/kyc_manager.html.twig', $renderViewData->toArray());
    }

    /**
     * Create and handle submitted KYC form of guest.
     *
     * @param Request $request
     * @Route("/guest", name="guest_kyc_show")
     *
     * @return Response
     */
    public function showGuest(Request $request): Response
    {
        try {
            $form = $this->createForm('MMUserBundle\Form\KycDocumentType');
            $form->handleRequest($request);
            $kycStatus = $this->checkKycStatusForGuest();
            if (null === $kycStatus) {
                if ($form->isSubmitted()) {
                    if ($form->isValid()) {
                        $flag = $this->validateMimeType($form);
                        if (0 === $flag) {
                            $kycResult = $this->createKyc($form);
                            $kycStatus = $this->checkKycStatusForGuest();
                        } else {
                            $this->logger->info('File type invalid error');
                            $form->addError(new FormError('Only image/jpeg, image/jpg, image/gif, image/png, application/pdf formats are accepted. Please check your file type.'));
                        }
                    } else {
                        $errors = $form->getErrors(true);
                        $form = $this->createForm('MMUserBundle\Form\KycDocumentType');
                        foreach ($errors as $error) {
                            $errorMsg = $error->getMessage();
                            $form->addError(new FormError($errorMsg));
                        }
                    }
                }
            }

            return $this->render('@MMUser/KYC/kyc_guest.html.twig', array(
                'kyc_document_type' => $form->createView(),
                'kyc_status' => $kycStatus,
            ));
        } catch (Exception $ex) {
            $this->logger->alert('Kyc Exception in guest form: '.$ex->getMessage());

            return $this->render('@MMUser/KYC/kyc_exceptionHandling.html.twig', array());
        }
    }

    /**
     * Handle submitted  kyc form of host.
     *
     * @param Request $request
     * @Route("/host_id", name="host_kyc_show")
     *
     * @return RedirectResponse
     */
    public function showHost(Request $request): RedirectResponse
    {
        try {
            $this->logger = $this->get('monolog.logger.mealmatch');
            $selectedTab = $request->get('selectedTab');
            if ('1' === $selectedTab) {
                $form = $this->createForm('MMUserBundle\Form\KycDocumentTypeID');
            } elseif ('2' === $selectedTab) {
                $form = $this->createForm('MMUserBundle\Form\KycDocumentTypeRP');
            } elseif ('3' === $selectedTab) {
                $form = $this->createForm('MMUserBundle\Form\KycDocumentTypeAA');
            } else {
                $form = $this->createForm('MMUserBundle\Form\KycDocumentTypeSD');
            }

            $form->handleRequest($request);

            $flag = 0;
            $errorMsg = null;
            if ($form->isSubmitted()) {
                $i = 0;
                if ($form->isValid()) {
                    $flag = $this->validateMimeType($form);
                    if (0 === $flag) {
                        $kycResult = $this->createKyc($form);
                    } else {
                        $errorMsg[$i] = 'Only image/jpeg, image/jpg, image/gif, image/png, application/pdf formats are accepted. Please check your file type.';
                    }
                } else {
                    $errors = $form->getErrors(true);
                    foreach ($errors as $error) {
                        $errorMsg[$i] = $error->getMessage();
                        ++$i;
                    }
                }
            }

            return $this->redirectToRoute('kyc_show', array('selectedTab' => $selectedTab, 'errorMessage' => $errorMsg));
        } catch (Exception $ex) {
            $this->logger->alert('Kyc Exception in host form: '.$ex->getMessage());

            return $this->render('@MMUser/KYC/kyc_exceptionHandling.html.twig', array());
        }
    }

    /**
     * Validate the mime type of the uploaded file.
     *
     * @Route("/check_type", name="check_mime_type")
     *
     * @return int
     */
    public function validateMimeType(FormInterface $form)
    {
        $mimeTypes = array('application/pdf', 'image/jpeg', 'image/jpg', 'image/gif', 'image/png');
        $files = $form->getData()->getKycDocCode();
        foreach ($files as $file) {
            $extension = $file->getMimeType();
            if (!\in_array($extension, $mimeTypes, true)) {
                return 1;
            }
        }

        return 0;
    }

    /*
     * Create KYC document and pages
     *
     * @Route("/validation/request", name="kyc_validation_request")
     * @return $kycDocResult| null
     */
    public function createKyc(Form $form)
    {
        try {
            $KycDocument = new \MangoPay\KycDocument();
            $entityManager = $this->get('doctrine.orm.default_entity_manager');
            $userPaymentPofile = $entityManager->getRepository('MMUserBundle:MMUserPaymentProfile')->findOneById($this->getUser()->getID());

            //Assume that every user got mangopay user id while registration
            $UserId = $userPaymentPofile->getMangopayID();
            $KycDocument->Type = $form->getData()->getKycDocSubmitted();
            $Result = $this->mangoPayApi->Users->CreateKycDocument($UserId, $KycDocument);

            $KYCDocumentId = $Result->Id;
            $KycPage = new \MangoPay\KycPage();
            $KycPage = $form->getData()->getKycDocCode();
            foreach ($KycPage as $kyc) {
                $kycPageResult = $this->mangoPayApi->Users->CreateKycPageFromFile($UserId, $KYCDocumentId, $kyc->getPathname());
            }

            $Result->Status = ApiConstants::VALIDATION_ASKED;
            $kycDocResult = $this->mangoPayApi->Users->UpdateKycDocument($UserId, $Result);
            $userId = $this->getUser()->getId();
            $kycProfile = new MMUserKYCProfile();
            $kycProfile->setUserID($userId);
            $kycProfile->setKycId($Result->Id);
            $kycProfile->setKycDocType($form->getData()->getKycDocType());
            $kycProfile->setMangopayUserID($Result->UserId);
            $kycProfile->setStatus($Result->Status);
            $kycProfile->setKycDocSubmitted($Result->Type);
            $user = $this->getUser();
            $user->setOverallKycStatus('Pending');
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->persist($kycProfile);
            $this->getDoctrine()->getManager()->flush();

            return $kycDocResult;
        } catch (MangoPay\Libraries\ResponseException $e) {
            $this->logger->alert('Kyc Response Exception: '.$e->getMessage());

            return $this->render('@MMUser/KYC/kyc_exceptionHandling.html.twig', array());
        } catch (MangoPay\Libraries\Exception $e) {
            $this->logger->alert('Kyc Exception: '.$e->getMessage());

            return $this->render('@MMUser/KYC/kyc_exceptionHandling.html.twig', array());
        }
    }

    /**
     * Check kyc status of Guest.
     *
     * @Route("/check_guest_status", name="check_kyc_guest_status")
     */
    public function checkKycStatusForGuest()
    {
        $entityManager = $this->get('doctrine.orm.default_entity_manager');
        $userPaymentPofile = $entityManager->getRepository('MMUserBundle:MMUserPaymentProfile')->findOneById($this->getUser()->getID());
        $userMangopayId = $userPaymentPofile->getMangopayID();
        $id = $entityManager->getRepository('MMUserBundle:MMUserKYCProfile')->findOneBy(array('mangopayUserID' => $userMangopayId, 'kycDocSubmitted' => 'IDENTITY_PROOF'));
        if (null === $id) {
            return null;
        }
        $kycStatus = $entityManager->getRepository('MMUserBundle:MMUserKYCProfile')->findStatusById($id);

        return $kycStatus[0]['status'];
    }

    /**
     * Check kyc status of Host.
     *
     * @Route("/check_host_status", name="check_kyc_host_status")
     */
    public function checkKycStatusForHost()
    {
        $entityManager = $this->get('doctrine.orm.default_entity_manager');
        $userPaymentPofile = $entityManager->getRepository('MMUserBundle:MMUserPaymentProfile')->findOneById($this->getUser()->getID());
        $userMangopayId = $userPaymentPofile->getMangopayID();
        $kycDocType = array('IDENTITY_PROOF', 'REGISTRATION_PROOF', 'ARTICLES_OF_ASSOCIATION', 'SHAREHOLDER_DECLARATION');
        $kycStatus = array();
        for ($i = 0; $i < 4; ++$i) {
            $id = $entityManager->getRepository('MMUserBundle:MMUserKYCProfile')->findOneBy(array('mangopayUserID' => $userMangopayId, 'kycDocSubmitted' => $kycDocType[$i]));
            if (null === $id) {
                $kycStatus[$i] = 'none';
            } else {
                $status = $entityManager->getRepository('MMUserBundle:MMUserKYCProfile')->findStatusById($id);
                $kycStatus[$i] = $status[0]['status'];
            }
        }

        return $kycStatus;
    }

    /**
     * KYC Event triggered by hooks.
     *
     * @Route("/kychooks",name="updateKyc", methods={"GET"})
     *
     * @param Request $request the HTTP Request to process
     *
     * @return Response
     */
    public function kycHooks(Request $request)
    {
        try {
            $entityManager = $this->get('doctrine.orm.default_entity_manager');
            $eventType = $request->get('EventType');
            $resourceId = $request->get('RessourceId');
            $response = new Response();
            $response->setStatusCode(200);
            /** @var MMUserKYCProfile $kyc */
            $kyc = $entityManager->getRepository('MMUserBundle:MMUserKYCProfile')->findOneByKycId($resourceId);
            $sender = $this->get('fos_message.sender');
            $userId = $kyc->getUserID();
            /** @var MMUser $user */
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
            $this->logger->alert('KYC hooks: 200 response');

            return $response;
        } catch (\Exception $ex) {
            $this->logger->alert('KYC hooks Exception: '.$ex->getMessage().
                                 'Exception code: '.$ex->getCode());

            return $response;
        }
    }

    /**
     * Helper to compose a valid FOS:Message from a SystemMessage using TWIG.
     *
     * @param SystemMessage $systemMessage
     *
     * @throws TwigError
     *
     * @return MessageInterfaceAlias
     */
    protected function composeMessage(
        SystemMessage $systemMessage
    ): MessageInterfaceAlias {
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

    /**
     * Creates the FORM for host.
     *
     * @param string $formTypeClass the FormType class to use
     * @param int    $selectedTab   the selectedTab value
     *
     * @return Form the Form as specified
     */
    private function createTabForm(string $formTypeClass, int $selectedTab): Form
    {
        $targetRoute = 'host_kyc_show';
        // create and return ...
        return $this->createForm(
            $formTypeClass,
            null,
            array(
                'action' => $this->generateUrl(
                    $targetRoute,
                    array('selectedTab' => $selectedTab)
                ),
                'method' => 'POST',
            )
        );
    }
}
