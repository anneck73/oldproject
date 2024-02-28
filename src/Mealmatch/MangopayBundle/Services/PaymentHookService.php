<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\MangopayBundle\Services;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use FOS\UserBundle\Security\LoginManager;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
use Mealmatch\ApiBundle\Entity\Meal\MealTicketTransaction;
use Mealmatch\ApiBundle\Exceptions\MealmatchException;
use MMUserBundle\Entity\MMUser;
use Psr\Log\LoggerInterface as Logger;
use ReflectionMethod;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Workflow;

class PaymentHookService extends BaseMangopayService
{
    /**
     * @var Workflow
     */
    private $workflow;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var LoginManager
     */
    private $loginManager;

    public function __construct(
        Logger $logger,
        EntityManager $entityManager,
        Translator $translator)
    {
        parent::__construct($logger, $entityManager, $translator);
    }

    /**
     * @param Workflow $workflow
     */
    public function setWorkflow(Workflow $workflow): void
    {
        $this->workflow = $workflow;
    }

    /**
     * @param TokenStorage $tokenStorage
     */
    public function setTokenStorage(TokenStorage $tokenStorage): void
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param LoginManager $loginManager
     */
    public function setLoginManager(LoginManager $loginManager)
    {
        $this->loginManager = $loginManager;
    }

    public function addTransactionFromHookEvent(array $hookData, BaseMealTicket $mealTicket): MealTicketTransaction
    {
        $this->systemUserLogin();
        $this->logger->debug('PaymentHookService->addTransactionFromHookEvent-------------------->START');
        $this->logger->debug('PaymentHookService->addTransactionFromHookEvent with $hookData('
            .json_encode($hookData).')');

        /** @var MealTicketTransaction $existingMTT */
        $existingMTT = $this->entityManager->getRepository('ApiBundle:Meal\MealTicketTransaction')->findOneBy(
            array('resourceId' => $hookData['rID'])
        );

        $newMTT = new MealTicketTransaction();
        $newMTT->setResourceId($hookData['rID']);
        $newMTT->setMealTicket($mealTicket);
        $newMTT->setMangoObj($hookData['mangoObj']);
        $newMTT->setMangoEvent($hookData['mangoEvent']);
        $newMTT->setMangoEventType($hookData['eventType']);
        $newMTT->setMangoNotifiedDate($hookData['date']);

        if (null !== $existingMTT) {
            $newMTT->setPaymentType($existingMTT->getPaymentType());
            $newMTT->setTransactionType($existingMTT->getTransactionType());
            $newMTT->setUserID($existingMTT->getUserID());
        }

        $mealTicket->addTransaction($newMTT);
        $this->entityManager->persist($mealTicket);
        $this->entityManager->persist($newMTT);
        $this->entityManager->flush();

        $this->logger->debug('PaymentHookService->addTransactionFromHookEvent done create ('.$newMTT->getId().')');
        $this->logger->debug('PaymentHookService->addTransactionFromHookEvent-------------------->END');

        return $newMTT;
    }

    /**
     * Process the MTT by calling the correct "process" Method.
     *
     * @param MealTicketTransaction $mealTicketTransaction
     *
     * @throws MealmatchException
     */
    public function processMealticketTransaction(MealTicketTransaction $mealTicketTransaction): void
    {
        $this->systemUserLogin();
        $this->logger->debug('-------------------->START');
        $this->logger->debug('ResourceID: '.$mealTicketTransaction->getResourceId());

        if ($mealTicketTransaction->isProcessed()) {
            $this->logger->debug('PaymentHookService->processMealticketTransaction, already processed('
            .$mealTicketTransaction->getResourceId().')');

            return; // Already processed!!!!
        }

        // /payment/PayIn/Created?RessourceId=60434285&EventType=PAYIN_NORMAL_CREATED&Date=1547557023
        // will call method ->processPayInCreated(PAYIN_NORMAL_CREATED);

        $factoryMethodName = ucfirst('process'.
            $mealTicketTransaction->getMangoObj().
            $mealTicketTransaction->getMangoEvent());

        // Create the reflection method
        try {
            $factoryMethod = new ReflectionMethod(self::class, $factoryMethodName);
        } catch (\ReflectionException $reflectionException) {
            throw new MealmatchException('Failed to create reflection for '.$factoryMethodName.
                ':'.$reflectionException->getMessage()
            );
        }

        // Overwrite accessor
        if ($factoryMethod->isPrivate()) {
            $factoryMethod->setAccessible(true);
        }
        // Invoke the method
        $processResults = $factoryMethod->invoke($this, $mealTicketTransaction->getMangoEventType(), $mealTicketTransaction);
        $this->logger->debug('PaymentHookService->'.$factoryMethodName.' results: '.json_encode($processResults));

        // Set processed to true ...
        $mealTicketTransaction->setProcessed(true);
        $this->logger->debug('PaymentHookService->'.$factoryMethodName.'-------------------->END');
    }

    /**
     * Forces a named system user login.
     */
    private function systemUserLogin(): void
    {
        /** @var MMUser $systemUser */
        $systemUser = $this->entityManager->getRepository('MMUserBundle:MMUser')->findOneByUsername('SYSTEM');
        $token = new UsernamePasswordToken($systemUser, $systemUser->getPassword(), 'main', $systemUser->getRoles());
        $this->tokenStorage->setToken($token);
        $this->loginManager->logInUser('main', $systemUser);
    }

    private function processTransferCreated(string $eventType, MealTicketTransaction $mealTicketTransaction)
    {
        $this->systemUserLogin();
        $this->logger->debug('PaymentHookService->processTransferCreated('.$eventType.')-------------------->START');

        $result = $this->doSetPaymentStatus($eventType, $mealTicketTransaction);

        $this->logger->debug('PaymentHookService->processTransferCreated('.$eventType.'), result status: '.
            json_encode($result));
        $this->logger->debug('PaymentHookService->processTransferCreated('.$eventType.')-------------------->END');

        return $result;
    }

    private function processTransferSucceeded(string $eventType, MealTicketTransaction $mealTicketTransaction)
    {
        $this->systemUserLogin();
        $this->logger->debug('PaymentHookService->processTransferSucceeded('.$eventType.')-------------------->START');

        $result = $this->doSetPaymentStatus($eventType, $mealTicketTransaction);

        $this->logger->debug('PaymentHookService->processTransferSucceeded('.$eventType.'), result status: '.
            json_encode($result));
        $this->logger->debug('PaymentHookService->processTransferSucceeded('.$eventType.')-------------------->END');

        return $result;
    }

    private function processTransferFailed(string $eventType, MealTicketTransaction $mealTicketTransaction)
    {
        $this->systemUserLogin();
        $this->logger->debug('PaymentHookService->processTransferFailed('.$eventType.')-------------------->START');

        $result = $this->doSetPaymentStatus($eventType, $mealTicketTransaction);

        $this->logger->debug('PaymentHookService->processTransferFailed('.$eventType.'), result status: '.
            json_encode($result));
        $this->logger->debug('PaymentHookService->processTransferFailed('.$eventType.')-------------------->END');

        return $result;
    }

    private function processPayOutCreated(string $eventType, MealTicketTransaction $mealTicketTransaction)
    {
        $this->systemUserLogin();
        $this->logger->debug('PaymentHookService->processPayOutCreated('.$eventType.')-------------------->START');

        $result = $this->doSetPaymentStatus($eventType, $mealTicketTransaction);

        $this->logger->debug('PaymentHookService->processPayOutCreated('.$eventType.'), result status: '.
            json_encode($result));
        $this->logger->debug('PaymentHookService->processPayOutCreated('.$eventType.')-------------------->END');

        return $result;
    }

    private function processPayOutSucceeded(string $eventType, MealTicketTransaction $mealTicketTransaction)
    {
        $this->systemUserLogin();
        $this->logger->debug('PaymentHookService->processPayOutSucceeded('.$eventType.')-------------------->START');

        $result = $this->doSetPaymentStatus($eventType, $mealTicketTransaction);

        $this->logger->debug('PaymentHookService->processPayOutSucceeded('.$eventType.'), result status: '.
            json_encode($result));
        $this->logger->debug('PaymentHookService->processPayOutSucceeded('.$eventType.')-------------------->END');

        return $result;
    }

    private function processPayOutFailed(string $eventType, MealTicketTransaction $mealTicketTransaction)
    {
        $this->systemUserLogin();
        $this->logger->debug('PaymentHookService->processPayOutFailed('.$eventType.')-------------------->START');

        $result = $this->doSetPaymentStatus($eventType, $mealTicketTransaction);

        $this->logger->debug('PaymentHookService->processPayOutFailed('.$eventType.'), result status: '.
            json_encode($result));
        $this->logger->debug('PaymentHookService->processPayOutFailed('.$eventType.')-------------------->END');

        return $result;
    }

    private function processPayInCreated(string $eventType, MealTicketTransaction $mealTicketTransaction)
    {
        $this->systemUserLogin();
        $this->logger->debug('PaymentHookService->processPayInCreated('.$eventType.')-------------------->START');

        $result = $this->doSetPaymentStatus($eventType, $mealTicketTransaction);

        $this->logger->debug('PaymentHookService->processPayInCreated('.$eventType.'), result status: '.
            json_encode($result));
        $this->logger->debug('PaymentHookService->processPayInCreated('.$eventType.')-------------------->END');

        return $result;
    }

    // /payment/PayIn/Succeeded

    /**
     * @param string                $eventType
     * @param MealTicketTransaction $mealTicketTransaction
     *
     * @return array
     */
    private function processPayInSucceeded(string $eventType, MealTicketTransaction $mealTicketTransaction)
    {
        $this->logger->debug('PaymentHookService->processPayInSucceeded('.$eventType.')-------------------->START');

        // Default change status according to type
        $result = $this->doSetPaymentStatus($eventType, $mealTicketTransaction);
        $this->logger->debug('PaymentHookService->processPayInSucceeded('.$eventType.'), result status: '.
            json_encode($result));

        if ($mealTicketTransaction->hasMealticket()) {
            // Get the mealticket
            $mealTicket = $this->entityManager->getRepository('ApiBundle:Meal\BaseMealTicket')->find(
                $mealTicketTransaction->getMealTicketId()
            );

            // apply payment_success with PayInSucceeded
            if ($this->workflow->can($mealTicket, 'payment_success')) {
                $this->workflow->apply($mealTicket, 'payment_success');
                $result = array(
                    'STATUS' => 'SUCCESS',
                    'MSG' => 'Applied payment_success transition to mealticket('.$mealTicket->getId().')',
                );
            } else {
                $result = array(
                    'STATUS' => 'ERROR',
                    'ERROR_MSG' => 'Failed to apply payment_success transition to mealticket('.$mealTicket->getId().')',
                );
            }
        } else {
            $this->logger->notice(
                'Transaction ('.$mealTicketTransaction->getResourceId().') has no Mealticket! skipped!');
        }

        $this->logger->debug('PaymentHookService->processPayInSucceeded('.$eventType.'), result status: '.
            json_encode($result));
        $this->logger->debug('PaymentHookService->processPayInSucceeded('.$eventType.')-------------------->END');

        return $result;
    }

    private function processPayInFailed(string $eventType, MealTicketTransaction $mealTicketTransaction)
    {
        $this->logger->debug('PaymentHookService->processPayInFailed('.$eventType.')-------------------->START');

        // Get the mealticket
        $mealTicket = $this->entityManager->getRepository('ApiBundle:Meal\BaseMealTicket')->find(
            $mealTicketTransaction->getMealTicketId()
        );
        // Default change status according to type
        $result = $this->doSetPaymentStatus($eventType, $mealTicketTransaction);
        $this->logger->debug('PaymentHookService->processPayInFailed('.$eventType.'), result status: '.
            json_encode($result));

        // apply payment_success after PayInSucceeded
        if ($this->workflow->can($mealTicket, 'payment_error')) {
            $this->workflow->apply($mealTicket, 'payment_error');
            $result = array(
                'STATUS' => 'SUCCESS',
                'MSG' => 'Applied payment_error transition to mealticket('.$mealTicket->getId().')',
            );
        } else {
            $result = array(
                'STATUS' => 'ERROR',
                'ERROR_MSG' => 'Failed to apply payment_error transition to mealticket('.$mealTicket->getId().')',
            );
        }

        $this->logger->debug('PaymentHookService->processPayInFailed('.$eventType.'), result status: '.
            json_encode($result));
        $this->logger->debug('PaymentHookService->processPayInFailed('.$eventType.')-------------------->END');

        return $result;
    }

    /**
     * Sets "PaymentStatus" to the value of the $eventType.
     *
     * @param string                $eventType
     * @param MealTicketTransaction $mealTicketTransaction
     *
     * @return array
     */
    private function doSetPaymentStatus(string $eventType, MealTicketTransaction $mealTicketTransaction): array
    {
        $mealTicketTransaction->setPaymentStatus($eventType);
        $mealTicket = $mealTicketTransaction->getMealTicket();
        $mealTicket->setLastPaymentStatus($eventType);
        try {
            $this->entityManager->persist($mealTicket);
            $this->entityManager->persist($mealTicketTransaction);
            $this->entityManager->flush();
            $result = array(
                'STATUS' => 'SUCCESS',
                'MSG' => 'Updated transaction status to '.$eventType,
            );
        } catch (OptimisticLockException $optimisticLockException) {
            $this->logger->error('PaymentHookService->Exception: '.$optimisticLockException);
            $result = array(
                'STATUS' => 'ERROR',
                'ERROR_MSG' => 'Exception: '.$optimisticLockException->getMessage(),
            );
        } catch (ORMException $ORMException) {
            $this->logger->error('PaymentHookService->Exception: '.$ORMException);
            $result = array(
                'STATUS' => 'ERROR',
                'ERROR_MSG' => 'Exception: '.$ORMException->getMessage(),
            );
        }

        return $result;
    }
}
