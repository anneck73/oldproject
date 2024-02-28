<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\MangopayBundle\Services;

use Doctrine\ORM\EntityManager;
use MangoPay\PayIn;
use MangoPay\PayOut;
use MangoPay\Transfer;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
use Mealmatch\ApiBundle\Entity\Meal\MealTicketTransaction;
use Mealmatch\ApiBundle\Exceptions\MealmatchException;
use Psr\Log\LoggerInterface as Logger;
use Symfony\Component\Translation\Translator;

/**
 * The MealticketTransactionService Class is responsible for handling Mangopay Transactions using ResourceIDs.
 */
class MealticketTransactionService extends BaseMangopayService
{
    public function __construct(Logger $logger, EntityManager $entityManager, Translator $translator)
    {
        parent::__construct($logger, $entityManager, $translator);
    }

    /**
     * @param PayIn $resultPayin
     *
     * @throws MealmatchException
     *
     * @return MealTicketTransaction
     */
    public function createFromPayin(PayIn $resultPayin): MealTicketTransaction
    {
        $this->logger->debug('MTT:createFromPayin----------->START');
        $this->logger->debug('MTT:createFromPayin->PayIn ID: '.$resultPayin->Id);

        try {
            $mealTicketTransaction = new MealTicketTransaction();
            $mealTicketTransaction->setUserID(0);
            $mealTicketTransaction->setPaymentType('PayIn');
            $mealTicketTransaction->setPaymentStatus('PayIn');
            $mealTicketTransaction->setResourceId($resultPayin->Id);
            $mealTicketTransaction->setMangoEvent('PayIn');
            $mealTicketTransaction->setMangoObj('Created');
            $mealTicketTransaction->setTransactionType(ApiConstants::TRANSACTION_TYPE_PAYIN);
            $this->entityManager->persist($mealTicketTransaction);
            $this->entityManager->flush();
            $this->logger->debug('MTT:createFromPayin----------->END');

            return $mealTicketTransaction;
        } catch (\Exception $e) {
            $this->logger->error('MTT:createFromPayin <---Exception: '.$e->getMessage());
            $mmEx = new MealmatchException('ERROR: Failed to create MealTicketTransaction');
            $mmEx->setFlashNotice('Testing:'.$e->getMessage());
            $mmEx->setModalError('Testing:'.$e->getMessage());
            throw $mmEx;
        }
    }

    /**
     * @param BaseMealTicket $mealTicket
     * @param PayIn          $resultPayin
     *
     * @throws MealmatchException
     *
     * @return MealTicketTransaction
     */
    public function createFromMealTicketAndPayin(BaseMealTicket $mealTicket, PayIn $resultPayin): MealTicketTransaction
    {
        $this->logger->debug('MTT:createFromPayin----------->START');
        $this->logger->debug('MTT:createFromPayin->BaseMealTicket: '.$mealTicket->getNumber());
        $this->logger->debug('MTT:createFromPayin->PayIn ID: '.$resultPayin->Id);

        try {
            $mealTicketTransaction = new MealTicketTransaction();
            $mealTicketTransaction->setUserID($mealTicket->getGuest()->getId());
            $mealTicketTransaction->setPaymentType($mealTicket->getPaymentType());
            $mealTicketTransaction->setPaymentStatus(ApiConstants::TRANSACTION_STATUS_CREATED);
            $mealTicketTransaction->setResourceId($resultPayin->Id);
            $mealTicketTransaction->setTransactionType(ApiConstants::TRANSACTION_TYPE_PAYIN);
            $mealTicketTransaction->setMealTicket($mealTicket);
            $this->entityManager->persist($mealTicketTransaction);

            $mealTicket->setResourceId($resultPayin->Id);
            $mealTicket->addTransaction($mealTicketTransaction);
            $this->entityManager->persist($mealTicket);

            $this->entityManager->flush();
            $this->logger->debug('MTT:createFromPayin----------->END');

            return $mealTicketTransaction;
        } catch (\Exception $e) {
            $this->logger->error('MTT:createFromPayin <---Exception: '.$e->getMessage());
            $mmEx = new MealmatchException('ERROR: Failed to create MealTicketTransaction');
            $mmEx->setFlashNotice('Testing:'.$e->getMessage());
            $mmEx->setModalError('Testing:'.$e->getMessage());
            throw $mmEx;
        }
    }

    public function createFromTransfer(BaseMealTicket $mealTicket, Transfer $resulTransfer)
    {
        if (null === $resulTransfer) {
            $this->logger->error('MTT:createFromTransfer-->Transfer was NULL!');

            return;
        }
        if (null === $resulTransfer->Id) {
            $this->logger->error('MTT:createFromTransfer-->resourceId was NULL!');

            return;
        }
        $this->logger->debug('MTT:createFromTransfer----------->START');
        $this->logger->debug('MTT:createFromTransfer->BaseMealTicket: '.$mealTicket->getNumber());
        try {
            $mealTicketTransaction = new MealTicketTransaction();
            $mealTicketTransaction->setUserID($mealTicket->getGuest()->getId());
            $mealTicketTransaction->setPaymentType($mealTicket->getPaymentType());

            if (null === $resulTransfer->Status) {
                $mealTicketTransaction->setPaymentStatus(ApiConstants::TRANSACTION_STATUS_FAILED);
            } else {
                $mealTicketTransaction->setPaymentStatus($resulTransfer->Status);
            }

            $mealTicketTransaction->setResourceId($resulTransfer->Id);
            $mealTicketTransaction->setTransactionType(ApiConstants::TRANSACTION_TYPE_TRANSFER);
            $mealTicketTransaction->setMealTicket($mealTicket);
            $this->entityManager->persist($mealTicketTransaction);

            // Transfer does not change resourceID of mealticket !!! $mealTicket->setResourceId($resultTransfer->Id);
            $mealTicket->addTransaction($mealTicketTransaction);
            $this->entityManager->persist($mealTicket);

            $this->entityManager->flush();
            $this->logger->debug('MTT:createFromTransfer----------->END');

            return $mealTicketTransaction;
        } catch (\Exception $e) {
            $this->logger->error('MTT:createFromTransfer <---Exception: '.$e->getMessage());
            $mmEx = new MealmatchException('ERROR: Failed to create MealTicketTransaction');
            $mmEx->setFlashNotice('Testing:'.$e->getMessage());
            $mmEx->setModalError('Testing:'.$e->getMessage());
            throw $mmEx;
        }
    }

    /**
     * @param BaseMealTicket $mealTicket
     * @param PayOut         $payOut
     *
     * @throws MealmatchException
     *
     * @return MealTicketTransaction
     */
    public function createFromPayout(BaseMealTicket $mealTicket, PayOut $payOut): ?MealTicketTransaction
    {
        $this->logger->debug('MTT:createFromPayout----------->START');
        $this->logger->debug('Using Payout---->'.json_encode($payOut));
        try {
            $mealTicketTransaction = new MealTicketTransaction();
            $mealTicketTransaction->setUserID($mealTicket->getGuest()->getId());
            $mealTicketTransaction->setPaymentType($mealTicket->getPaymentType());

            if (null === $payOut->Status) {
                $mealTicketTransaction->setPaymentStatus(ApiConstants::PAYOUT_STATUS_FAILED);
            } else {
                $mealTicketTransaction->setPaymentStatus($payOut->Status);
            }

            $mealTicketTransaction->setResourceId($payOut->Id);
            $mealTicketTransaction->setTransactionType(ApiConstants::TRANSACTION_TYPE_PAYOUT);
            $mealTicketTransaction->setMealTicket($mealTicket);
            $this->entityManager->persist($mealTicketTransaction);

            // Transfer does not change resourceID of mealticket !!! $mealTicket->setResourceId($resultTransfer->Id);
            $mealTicket->addTransaction($mealTicketTransaction);
            $this->entityManager->persist($mealTicket);

            $this->entityManager->flush();
            $this->logger->debug('MTT:createFromPayout----------->END');

            return $mealTicketTransaction;
        } catch (\Exception $e) {
            $this->logger->error('MTT:createFromPayout <---Exception: '.$e->getMessage());
            $mmEx = new MealmatchException('ERROR: Failed to create MealTicketTransaction');
            $mmEx->setFlashNotice('Testing:'.$e->getMessage());
            $mmEx->setModalError('Testing:'.$e->getMessage());
            throw $mmEx;
        }
    }
}
