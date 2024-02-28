<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\PayPalBundle\Services;

use Doctrine\ORM\EntityManager;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
use Mealmatch\PayPalBundle\Entity\PayPalPaymentToken;
use Mealmatch\PayPalBundle\Exceptions\PayPalException;
use Mealmatch\PayPalBundle\PayPalConstants;
use MMApiBundle\Entity\MealTicket;
use Monolog\Logger;
use OpenBuildings\PayPal\Payment_Adaptive_Chained;

/**
 * @todo: Finish PHPDoc!
 * The PaymentTokenService is responsible for verifying and persisting the request and response tokens of the PayPal
 *      communication.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class PaymentTokenService
{
    const PAY_PAL_EXCEPTION = 'PayPalException';
    const PAYMENT_RESULT = 'Payment Result';
    const USING_SERVICE_CLASS = 'Using Service Class';
    const MEAL_ENTITY_CLASS = 'MMApiBundle:Meal';

    /**
     * The logger service is used to write log entries.
     *
     * @var Logger
     */
    private $logger;
    /**
     * The entity manager is used to persist the PayPalPaymentToken.
     *
     * @var EntityManager
     */
    private $entityManager;

    /**
     * PaymentTokenService constructor.
     *
     * @param EntityManager $entityManager the entity manager used
     * @param Logger        $logger        the logger class used
     */
    public function __construct(EntityManager $entityManager, Logger $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * @todo: Finish PHPDoc!
     * Creates a PayPalPaymentToken for a mealTicket using the IPN Notification values.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param BaseMealTicket $mealTicket       the mealTicket associated with this IPN notification
     * @param array          $notifyPostValues the values of the IPN notification
     *
     * @return PayPalPaymentToken
     */
    public function createOnNotify(BaseMealTicket $mealTicket, array $notifyPostValues): PayPalPaymentToken
    {
        $this->logger->addError(PayPalConstants::logPrefix($mealTicket->getNumber()).'PaymentTokenService:createOnNotify!');

        /** @var PayPalPaymentToken $paymentNotifyToken */
        $paymentNotifyToken = new PayPalPaymentToken();
        $paymentNotifyToken->setMealTicket($mealTicket);
        $paymentNotifyToken->setTokenResp($notifyPostValues);
        $paymentNotifyToken->setTokenStatus($notifyPostValues['status']);
        $paymentNotifyToken->setTokenKey($notifyPostValues['pay_key']);
        $this->entityManager->persist($paymentNotifyToken);
        $this->entityManager->persist($mealTicket);
        $this->entityManager->flush();

        return $paymentNotifyToken;
    }

    /**
     * Creates a PayPalPaymentToken to track the results from the request<->response with paypal using adaptive_chained.
     *
     * It sets token-key, token-status and token-error if contained in the response.
     *
     * @param Payment_Adaptive_Chained $adaptiveChained
     * @param array                    $result
     * @param MealTicket               $mealTicket
     *
     * @throws PayPalException if the response contains a PayPalException or if the response could not be validated
     *
     * @return PayPalPaymentToken containing request, response and result's including status and errors
     */
    public function trackAdaptiveChainedTicketResult(
        Payment_Adaptive_Chained $adaptiveChained,
        array $result,
        BaseMealTicket $mealTicket
    ): PayPalPaymentToken {
        /**
         * To track payment req<->resp communication.
         */
        $payToken = new PayPalPaymentToken();
        $payToken->setMealTicket($mealTicket);
        $mealTicket->addPaymentTokens($payToken);

        $payToken->setTokenReq(
            array('Service' => $adaptiveChained::$instances, 'fields' => $adaptiveChained->fields())
        );

        // we set the token response "as is" without any modification ...
        $payToken->setTokenResp($result[static::PAYMENT_RESULT]);

        // if there is not payKey, we need to fail ...
        if (empty($result[static::PAYMENT_RESULT]['payKey'])) {
            $payToken->setTokenError(array('NoPayKey'));
            $this->entityManager->persist($payToken);
            $this->entityManager->persist($mealTicket);
            $this->entityManager->flush();

            throw new PayPalException(
                'A PayPal Error(NoPayKey) occured!'
                .'We apologize for the inconvenience, our service team has been notified!'
            );
        }
        // PayKey exists, set it
        $payToken->setTokenKey($result[static::PAYMENT_RESULT]['payKey']);

        // if there is no payment execution status, we need to fail ...
        if (empty($result[static::PAYMENT_RESULT]['paymentExecStatus'])) {
            $payToken->setTokenError(array('NoExecStatus'));
            $this->entityManager->persist($payToken);
            $this->entityManager->persist($mealTicket);
            $this->entityManager->flush();

            throw new PayPalException(
                'A PayPal Error(NoExecStatus) occured!'
                .'We apologize for the inconvenience, our service team has been notified!'
            );
        }
        // Not empty, set it
        $payToken->setTokenStatus($result[static::PAYMENT_RESULT]['paymentExecStatus']);

        // if we have a PayPalException, we need to fail ...
        if (!empty($result[static::PAYMENT_RESULT][static::PAY_PAL_EXCEPTION])) {
            $tokenError = array($result[static::PAYMENT_RESULT][static::PAY_PAL_EXCEPTION]);
            $payToken->setTokenError($tokenError);
            $this->entityManager->persist($payToken);
            $this->entityManager->persist($mealTicket);
            $this->entityManager->flush();

            $this->logger->addEmergency('PayPalException: '.json_encode($tokenError).' #'.$mealTicket->getNumber());

            throw new PayPalException(
                $result[static::PAYMENT_RESULT][static::PAY_PAL_EXCEPTION]
            );
        }

        // No Exception thrown, all good, safe it.
        $this->logger->addInfo(
            'PaymentToken: '.$payToken.' updated #'.$mealTicket->getNumber());

        $this->entityManager->persist($payToken);
        $this->entityManager->persist($mealTicket);
        $this->entityManager->flush();

        return $payToken;
    }
}
