<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\WorkflowBundle\Event\Subscriber;

use Doctrine\ORM\EntityManager;
use FOS\MessageBundle\Composer\Composer;
use FOS\MessageBundle\Sender\Sender;
use MangoPay\MangoPayApi;
use MangoPay\PayIn;
use MangoPay\Transfer;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Entity\Coupon\MealCoupon;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
use Mealmatch\ApiBundle\Exceptions\MealmatchException;
use Mealmatch\ApiBundle\Services\MealTicketService;
use Mealmatch\CouponBundle\Services\PublicCouponService;
use Mealmatch\MangopayBundle\Services\MangopayApiService;
use Mealmatch\MangopayBundle\Services\MealticketTransactionService;
use Mealmatch\MangopayBundle\Services\PublicMangopayService;
use MMUserBundle\Entity\MMUser;
use Monolog\Logger;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Workflow\Event\Event;

class MealTicketActions extends AbstractMealTicketSubscriber implements EventSubscriberInterface
{
    /**
     * @var MangoPayApi;
     */
    private $mangoPayApi;

    /**
     * @var MealticketTransactionService
     */
    private $mttService;
    /**
     * @var PublicMangopayService
     */
    private $mangopayService;
    /**
     * @var PublicCouponService
     */
    private $couponService;

    /**
     * MealTicketActions constructor.
     *
     * @param Logger                       $logger
     * @param EntityManager                $entityManager
     * @param MealTicketService            $mealTicketService
     * @param MealticketTransactionService $mealticketTransactionService
     * @param MangopayApiService           $mangopayApiService
     * @param Composer                     $composer
     * @param Sender                       $sender
     * @param TokenStorage                 $storage
     * @param TwigEngine                   $twigEngine
     */
    public function __construct(
        Logger $logger,
        EntityManager $entityManager,
        MealTicketService $mealTicketService,
        MealticketTransactionService $mealticketTransactionService,
        MangopayApiService $mangopayApiService,
        PublicMangopayService $mangopayService,
        PublicCouponService $couponService,
        Composer $composer,
        Sender $sender,
        TokenStorage $storage,
        TwigEngine $twigEngine
    ) {
        parent::__construct($logger, $entityManager, $mealTicketService, $composer, $sender, $storage, $twigEngine);
        $this->mttService = $mealticketTransactionService;
        $this->mangoPayApi = $mangopayApiService->getMangopayApi();
        $this->mangoPayApi->logger = $this->logger;
        $this->mangopayService = $mangopayService;
        $this->couponService = $couponService;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents(): array
    {
        return array(
            'workflow.meal_ticket.transition.create_ticket' => array(
                array('createTicket', 1),
                array('safeSubject', 2),
            ),
            'workflow.meal_ticket.transition.pay_ticket' => array(
                array('payTicket', 1),
                array('safeSubject', 2),
            ),
            'workflow.meal_ticket.transition.payment_retry' => array(
                array('payTicket', 1),
                array('safeSubject', 2),
            ),
            'workflow.meal_ticket.transition.payment_error' => array(
                array('payError', 1),
                array('safeSubject', 2),
            ),
            'workflow.meal_ticket.transition.payment_success' => array(
                array('paySuccess', 1),
                array('safeSubject', 2),
            ),
            'workflow.meal_ticket.transition.cancel_ticket' => array(
                array('cancelTicket', 1),
                array('safeSubject', 2),
            ),
            'workflow.meal_ticket.transition.use_ticket' => array(
                array('useTicket', 1),
                array('safeSubject', 2),
            ),
            'workflow.meal_ticket.transition.redeem_coupon' => array(
                array('redeemCoupon', 1),
                array('safeSubject', 2),
            ),
            'workflow.meal_ticket.transition.prepare_payment' => array(
                array('prepareTicket', 1),
                array('safeSubject', 2),
            ),
            // finish_prepare_ticket
            'workflow.meal_ticket.transition.finish_prepare_payment' => array(
                array('finishPrepareTicket', 1),
                array('safeSubject', 2),
            ),
        );
    }

    public function redeemCoupon(Event $event)
    {
        $this->logger->alert('MealTicket Transition: '.$event->getTransition()->getName());
    }

    public function useTicket(Event $event)
    {
        $this->logger->alert('MealTicket Transition: '.$event->getTransition()->getName());
    }

    public function cancelTicket(Event $event)
    {
        $this->logger->alert('MealTicket Transition: '.$event->getTransition()->getName());
    }

    public function createTicket(Event $event): void
    {
        $this->logger->alert('MealTicket Transition: '.$event->getTransition()->getName());
    }

    public function finishPrepareTicket(Event $event)
    {
        $this->logger->alert('MealTicket Transition: '.$event->getTransition()->getName());
    }

    /**
     * @param Event $event - The event containing the BaseMealTicket
     */
    public function payTicket(Event $event): void
    {
        /* @var BaseMealTicket $mealTicket */
        $mealTicket = $event->getSubject();
        /** @var MMUser $guest */
        $guest = $mealTicket->getGuest();

        /** @var string $ticketNumber */
        $ticketNumber = $mealTicket->getNumber();

        $this->logger->debug("payTicket($ticketNumber)");

        // Special Hack for event using orig. meal price (no coupon!)
        if ($mealTicket->getPrice() < 1) {
            $this->addGuestToMeal($mealTicket);
            $mealTicket->setStatus(ApiConstants::MEAL_TICKET_STATUS_PAYED);

            return;
        }

        // Special Hack for 0€ after Coupon as been factored in and matches exactly to 0!
        if (0 === $mealTicket->getTotalPriceInCent()) {
            $this->noPayinRequired($mealTicket);

            return;
        }

        // MealCoupon, grants access to meals without payment.
        if (null !== $mealTicket->getCoupon()) {
            $coupon = $mealTicket->getCoupon();
            if ('MealCoupon' === $coupon->getType()) {
                $this->noPayinRequired($mealTicket);

                return;
            }
        }

        //
        // ================== Now remote MangopayService is called
        //

        // Create the payInDirectWeb Object using the $mealTicket
        /** @var PayIn $payIn */
        $payIn = $this->mangopayService->getMangopayPayInService()->createPayInDirectWeb($mealTicket);

        // Send the payInDirectWeb request
        $resultPayIn = $this->mangopayService->getMangopayPayInService()->doCreatePayInDirectWeb($payIn);

        // Create the local meal ticket transaction to store the resourceID in
        try {
            $this->mttService->createFromMealTicketAndPayin($mealTicket, $resultPayIn);
        } catch (MealmatchException $mealmatchException) {
            $this->logger->error('Failed to create MTT -> '.$mealmatchException->getMessage());
        }

        if (null !== $resultPayIn && $resultPayIn->ExecutionDetails && $resultPayIn->ExecutionDetails->RedirectURL) {
            $mealTicket->setRedirectURL($resultPayIn->ExecutionDetails->RedirectURL);
            $this->logger->alert('payTicket: Set redirect URL for BaseMealTIcket to: '.$mealTicket->getRedirectURL());
            $mealTicket->setPayInStatus($resultPayIn->Status);
            $this->logger->alert('payTicket: Set PayInStatus to: '.$mealTicket->getPayInStatus());
        } else {
            $mealTicket->setPayInStatus(ApiConstants::MEAL_TICKET_PAYIN_STATUS_NOT_CREATED);
            $this->logger->alert('payTicket: Failed to createPayInDirectWeb: '.$mealTicket->getPayInStatus());
        }

        $this->logger->alert('payTicket: MealTicket Transition: '.$event->getTransition()->getName().' finished.');
    }

    public function prepareTicket(Event $event)
    {
        $this->logger->alert('prepareTicket: MealTicket Transition: '.$event->getTransition()->getName().' logged.');
    }

    public function payError(Event $event)
    {
        /** @var BaseMealTicket $mealticket */
        $mealticket = $event->getSubject();
        $this->logger->alert('MealTicket('.$mealticket->getNumber().') Transition: '.$event->getTransition()->getName());
    }

    public function paySuccess(Event $event)
    {
        /** @var BaseMealTicket $mealticket */
        $mealticket = $event->getSubject();
        $this->logger->alert('MealTicket('.$mealticket->getNumber().') Transition: '.$event->getTransition()->getName());

        // PayIN successful, put the guest into the meal
        $this->addGuestToMeal($mealticket);
        // and start coupon e-money value transfer from coupon-wallet into guest-wallet.
        $this->processCoupon($mealticket);
    }

    /**
     * This method adds the guest of the BaseMealTicket into the BaseMeal:guests collection.
     * Under the following condition:
     * Home-Meal: Only one guest per Meal
     * Pro-Meal: No limit!
     *
     * @param BaseMealTicket $mealTicket
     */
    protected function addGuestToMeal(BaseMealTicket $mealTicket)
    {
        $baseMeal = $mealTicket->getBaseMeal();
        if (null === $baseMeal) {
            // WTF!!
            $this->logger->addNotice(
                'AddGuestToMeal FAILED! ->BaseMeal was NULL!'
            );

            return;
        }
        $mealType = $baseMeal->getMealType();
        if ('ProMeal' === $mealType) {
            $baseMeal->addGuest($mealTicket->getGuest());
            $this->logger->addNotice(
                sprintf('Added Guest(%s) to ProMeal(%s) with MealOffer(%s)!',
                    $mealTicket->getGuest(), $mealTicket->getBaseMeal(), $mealTicket->getSelectedMealOffer())
            );
        } elseif ('HomeMeal' === $mealType) {
            if ($baseMeal->isGuest($mealTicket->getGuest())) {
                $this->logger->addError(
                    'User '.$mealTicket->getGuest().' is already a guest in the meal:'
                    .$mealTicket->getBaseMeal()
                );

                return;
            }
            $baseMeal->addGuest($mealTicket->getGuest());
            $this->logger->addNotice(
                sprintf('Added Guest(%s) to HomeMeal(%s)!', $mealTicket->getGuest(), $mealTicket->getBaseMeal())
            );
        }
    }

    /**
     * Execute a "transfer" from CouponWallet to GuestWallet.
     *
     * @param BaseMealTicket $mealticket
     */
    protected function processCoupon(BaseMealTicket $mealticket): void
    {
        // Only if there is a Coupon....
        if (null !== $mealticket->getCoupon()) {
            try {
                /** @var Transfer $transfer */
                $transfer = $this->mangopayService->createTransferCouponToGuestWallet($mealticket);
                $transferResult = $this->mangopayService->executeTransfer($transfer);
                $this->mttService->createFromTransfer($mealticket, $transferResult);
            } catch (MealmatchException $mealmatchException) {
                $this->logger->error('paySuccess()--->Failed to createMTT: '.
                    $mealmatchException->getMessage());
            } catch (\Exception $exception) {
                $this->logger->error('paySuccess()--->Failed to createMTT: '.
                    $exception->getMessage());
            }

            if (ApiConstants::TRANSACTION_STATUS_FAILED === $transferResult->Status) {
                $this->logger->error('paySuccess()--->Failed to execute Coupon Transfer: '.
                    $transferResult->ResultCode.'#'.$transferResult->ResultMessage);
            }
        }
    }

    /**
     * @param BaseMealTicket $mealTicket
     */
    private function noPayinRequired(BaseMealTicket $mealTicket): void
    {
        // No PAYIN required by guest, identified by a MealCoupon
        $this->addGuestToMeal($mealTicket);
        $this->processCoupon($mealTicket);
        $mealTicket->setStatus(ApiConstants::MEAL_TICKET_STATUS_PAYED);
    }
}
