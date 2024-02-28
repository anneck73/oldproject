<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\PayPalBundle\Services;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
use Mealmatch\PayPalBundle\Entity\PayPalPaymentToken;
use Mealmatch\PayPalBundle\Event\PayPalIPN;
use Mealmatch\PayPalBundle\PayPalConstants;
use Mealmatch\PayPalBundle\PayPalStatusValues;
use MMApiBundle\Entity\JoinRequest;
use MMApiBundle\Entity\Meal;
use MMApiBundle\Entity\MealTicket;
use Monolog\Logger;
use OpenBuildings\PayPal\Exception;
use OpenBuildings\PayPal\Payment;
use OpenBuildings\PayPal\Payment_Adaptive_Chained;
use OpenBuildings\PayPal\Payment_Adaptive_Simple;
use OpenBuildings\PayPal\Request_Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Workflow\Workflow;

class PayPalManagerService
{
    const CONFIG_USERNAME = 'username';
    const CONFIG_PW = 'password';
    const CONFIG_SIGNATURE = 'signature';
    const CONFIG_EMAIL = 'email';
    const CONFIG_SANDBOX = 'sandbox';
    const CONFIG_LIVE = 'live';

    /** @var array the internale paypal credentials */
    private $credentials;

    /** @var Logger $logger the logger to use */
    private $logger;

    /** @var EntityManager $em the ORM to use */
    private $em;

    /** @var Router */
    private $router;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var Workflow $workflow */
    private $workflow;

    /**
     * MMPayPalManager constructor.
     *
     * @param EntityManager $pEm
     * @param Logger        $pLog
     * @param Router        $pRouter
     * @param array         $parameters
     */
    public function __construct(
        EntityManager $pEm,
        Logger $pLog,
        EventDispatcherInterface $dispatcher,
        Router $pRouter,
        array $parameters
    ) {
        $this->em = $pEm;
        $this->logger = $pLog;
        $this->router = $pRouter;
        $this->dispatcher = $dispatcher;
        $this->credentials = $parameters;
        // $this->logger->addDebug('Created '.__CLASS__.' with CREDENTIALS: '.json_encode($parameters));
        // This should only work on FORTRABBIT
        $appName = getenv('APP_NAME');
        if (false !== $appName && 'mealmatch-stage' === $appName) {
            // Set PayPal to sandbox mode on mealmatch-stage only
            $this->credentials[self::CONFIG_LIVE] = false;
            $this->logger->addDebug(
                'AUTO-SET SANDBOX MODE! mealmatch-stage with CREDENTIALS: '.json_encode($parameters));
        }
    }

    /**
     * Returns a configured Payment Service.
     *
     * @param string $serviceName the name of the payment Service
     *
     * @return PayPalManagerService
     */
    public function getByService(string $serviceName)
    {
        $this->logger->addDebug('Service used: '.$serviceName);

        /** @var Payment_Adaptive_Chained $pService */
        $pService = Payment::instance($serviceName);

        $this->logger->addDebug('Credentials: '.json_encode($this->credentials));
        // depending on live setting, choose environment and app_id
        if ($this->credentials[self::CONFIG_LIVE]) {
            $this->logger->addDebug('PAYPAL LIVE!!!');
            $pService::environment(Payment::ENVIRONMENT_LIVE);
            $pService->config(PayPalConstants::CONFIG_APP_ID, $this->credentials[PayPalConstants::CONFIG_APP_ID]);
            $pService->config(self::CONFIG_USERNAME, $this->credentials[self::CONFIG_USERNAME]);
            $pService->config(self::CONFIG_PW, $this->credentials[self::CONFIG_PW]);
            $pService->config(self::CONFIG_SIGNATURE, $this->credentials[self::CONFIG_SIGNATURE]);
            $pService->config(self::CONFIG_EMAIL, $this->credentials[self::CONFIG_EMAIL]);
        } else {
            $this->logger->addDebug('PAYPAL SANDBOX!!!');
            $this->logger->addDebug('USING CONFIG '.json_encode($this->credentials[self::CONFIG_SANDBOX]));
            $pService::environment(Payment::ENVIRONMENT_SANDBOX);
            $pService->config(PayPalConstants::CONFIG_APP_ID, $this->credentials[self::CONFIG_SANDBOX][PayPalConstants::CONFIG_APP_ID]);
            $pService->config(self::CONFIG_USERNAME, $this->credentials[self::CONFIG_SANDBOX][self::CONFIG_USERNAME]);
            $pService->config(self::CONFIG_PW, $this->credentials[self::CONFIG_SANDBOX][self::CONFIG_PW]);
            $pService->config(self::CONFIG_SIGNATURE, $this->credentials[self::CONFIG_SANDBOX][self::CONFIG_SIGNATURE]);
            $pService->config(self::CONFIG_EMAIL, $this->credentials[self::CONFIG_SANDBOX][self::CONFIG_EMAIL]);
        }
        // this is default, will be overwritten by each payment call
        $pService->config('currency', 'EU');

        return $pService;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param Payment_Adaptive_Chained $pAdaptiveChained
     * @param BaseMealTicket           $pMealTicket
     *
     * @return array
     */
    public function doPayment(Payment_Adaptive_Chained $pAdaptiveChained, BaseMealTicket $pMealTicket): array
    {
        $this->logger->addInfo(
            'doPayment with Ticket: '.$pMealTicket
        );

        $price = $pMealTicket->getPrice();
        $currency = $pMealTicket->getCurrency();
        $quantity = $pMealTicket->getNumberOfTickets();
        $fee = $pMealTicket->getMmFee();
        $hostShare = $price - $fee;

        $this->logger->addInfo(
            'HostShare: '.$hostShare.' Fee: '.$fee
        );

        if (ApiConstants::MEAL_TYPE_PRO === $pMealTicket->getBaseMeal()->getMealType()) {
            $hostPayPalMail = $pMealTicket->getHost()->getRestaurantProfile()->getPayPalEmail();
        }

        if (ApiConstants::MEAL_TYPE_HOME === $pMealTicket->getBaseMeal()->getMealType()) {
            if (null === $pMealTicket->getHost()->getProfile()->getPayPalEmail()) {
                $hostPayPalMail = $pMealTicket->getHost()->getEmail();
            } else {
                $hostPayPalMail = $pMealTicket->getHost()->getProfile()->getPayPalEmail();
            }
        }

        $this->logger->addInfo('HostPayPalEmail: '.$hostPayPalMail);
        $mealmatchEail = $this->credentials[self::CONFIG_LIVE] ? $this->credentials[self::CONFIG_EMAIL] : $this->credentials[self::CONFIG_SANDBOX][self::CONFIG_EMAIL];
        $this->logger->addInfo('MealmatchEmail: '.$mealmatchEail);

        $cancelURL = $this->router->generate(
            'paypal_cancel',
            array('hash' => $pMealTicket->getHash()),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $returnURL = $this->router->generate(
            'paypal_success',
            array('hash' => $pMealTicket->getHash()),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $notifyURL = $this->router->generate(
            'paypal_notify',
            array('hash' => $pMealTicket->getHash()),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        /** @var Payment_Adaptive_Chained $paymentService */
        $paymentService = $pAdaptiveChained
            ->config('fees_payer', Payment_Adaptive_Simple::FEES_PAYER_SENDER)
            ->config('currency', $currency)
            // leave this empty so user can fill it ...
            ->config(self::CONFIG_EMAIL, '')
            ->order(
                array(
                    'total_price' => $price,
                    'item_name' => 'Mealmatch: '.$pMealTicket->getTitel(),
                    'item_description' => $pMealTicket->getDescription(),
                    'item_quantity' => $quantity,
                    'item_number' => $pMealTicket->getNumber(),
                    'receivers' => array(
                        array(
                            self::CONFIG_EMAIL => $hostPayPalMail,
                            'amount' => $hostShare,
                            'item_name' => 'Mealmatch: '.$pMealTicket->getTitel(),
                            'item_description' => 'Mealmatch '.$pMealTicket->getDescription(),
                        ),
                        array(
                            self::CONFIG_EMAIL => $this->credentials[self::CONFIG_LIVE] ? $this->credentials[self::CONFIG_EMAIL] : $this->credentials[self::CONFIG_SANDBOX][self::CONFIG_EMAIL],
                            'amount' => $fee,
                            'item_name' => 'Mealmatch: '.$pMealTicket->getTitel(),
                            'item_description' => 'Vermittlungsgebühr für Ticket#'.$pMealTicket->getNumber(),
                        ),
                    ),
                )
            )
            ->return_url($returnURL)
            ->cancel_url($cancelURL)
            ->notify_url($notifyURL)
            ->implicit_approval(true)
        ;

        $this->logger->addInfo(
            'PaymentServiceCall: '.
            json_encode($paymentService->fields())
        );
        // Execute the payment ...
        try {
            $paymentResult = $paymentService->do_payment();
        } catch (Request_Exception $requestException) {
            $paymentResult = array(
                'PayPalException' => $requestException->getMessage(),
            );

            $this->logger->addError('Payment failed: '.$requestException->getMessage().' Ticket#'.$pMealTicket->getNumber());
        } catch (Exception $exception) {
            $paymentResult = array(
                'PayPalException' => $exception->getMessage(),
            );

            $this->logger->addError('Payment failed: '.$exception->getMessage()
                .' Ticket#'.$pMealTicket->getNumber());
        }

        $this->logger->addInfo('Payment Result: '.json_encode($paymentResult)
            .' Ticket#'.$pMealTicket->getNumber());

        return $paymentResult;
    }

    /**
     * Main logic to match PayPal IPN Status to MealTicket::status.
     *
     * The MealTicket will use the PayPalPaymentToken to determine its NEW status.
     * The MealTicket is persisted with the NEW status matching MealTicketStatusValues constant.
     *
     * @param MealTicket         $mealTicket
     * @param PayPalPaymentToken $paymentToken
     */
    public function updateMealTicketOnNotify(BaseMealTicket $mealTicket, PayPalPaymentToken $paymentToken)
    {
        $VALID_VALUES = array(
            PayPalStatusValues::IPN_STATUS_COMPLETED,
            PayPalStatusValues::IPN_STATUS_PROCESSED,
            PayPalStatusValues::IPN_STATUS_CREATED,
        );
        // Depending on IPN Status, we set MealTicket:Status.
        if (\in_array($paymentToken->getTokenStatus(), $VALID_VALUES, true)) {
            // We are in the positive array ...
            $this->logger->addInfo(
                'PayPal-IPN/Notify Status: '.$paymentToken->getTokenStatus().' is OK!'
                .' Ticket#'.$mealTicket->getNumber()
            );
            // Update mealTicket ...
            // The workflow will add the user into the guests list
            $this->workflow->apply($mealTicket, 'payment_success');
            $this->logger->addInfo(
                'PayPal-IPN/Notify transition: payment_success executed!'
                .' Ticket#'.$mealTicket->getNumber()
            );
            $this->logger->addInfo('Mealtickt for payment token ('.$paymentToken.')'.': '.$mealTicket);
            $mealType = $mealTicket->getBaseMeal()->getMealType();
            if (ApiConstants::MEAL_TYPE_HOME === $mealType) {
                // Update the JoinRequest to "Payed=true"
                $this->setJoinRequestPayed($mealTicket);
                $this->logger->addInfo(
                    'PayPal-IPN/Notify HomeTicket setJoinRequestPayed ('.$mealTicket.')!'
                    .' Ticket#'.$mealTicket->getNumber()
                );
            }
        } else {
            // WEBAPP-109 ... only write a log file about this!
            $this->logger->addError(
                'Default/Unknown! PayPal-IPN/Notify Status: '.$paymentToken->getTokenStatus()
                .' Ticket#'.$mealTicket->getNumber()
            );

            return;
        }

        // update MealTicket ...
        // WEBAPP-109 ... adding try / catch
        try {
            $this->em->persist($mealTicket);
        } catch (ORMException $ORMException) {
            // WEBAPP-109 ... only write a log file about this!
            $this->logger->addError(
                'Could not safe MealTicket!! -> '.$ORMException->getMessage()
            );
        }

        // Dispatch Event about PayPal IPN with MealTicket.
        $this->dispatcher->dispatch(PayPalIPN::EVENT_NAME, new PayPalIPN($mealTicket));
    }

    public function setWorkflow(Workflow $workflow)
    {
        $this->workflow = $workflow;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param BaseMealTicket $mealTicket
     */
    protected function setJoinRequestPayed(BaseMealTicket $mealTicket)
    {
        /** @var Meal $meal */
        $meal = $mealTicket->getBaseMeal();
        $guest = $mealTicket->getGuest();
        $jReqs = $meal->getJoinRequests();
        /** @var JoinRequest $joinReq */
        foreach ($jReqs as $joinReq) {
            if ($joinReq->getCreatedBy() === $guest) {
                $joinReq->setPayed(true);
            }
        }
    }

    protected function setJoinRequestPaymentError(BaseMealTicket $mealTicket)
    {
        /** @var Meal $meal */
        $meal = $mealTicket->getBaseMeal();
        $guest = $mealTicket->getGuest();
        $jReqs = $meal->getJoinRequests();
        /** @var JoinRequest $joinReq */
        foreach ($jReqs as $joinReq) {
            if ($joinReq->getCreatedBy() === $guest) {
                $joinReq->setStatus(JoinRequest::$STATUS_PAYMENT_FAILED);
                $joinReq->setPayed(false);
            }
        }
    }

    protected function executePaymentSuccess(BaseMealTicket $mealTicket)
    {
        // The workflow will add the user into the guests list
        $this->workflow->apply($mealTicket, 'payment_success');
        // Update the JoinRequest to "Payed=true"
        $this->setJoinRequestPayed($mealTicket);
    }
}
