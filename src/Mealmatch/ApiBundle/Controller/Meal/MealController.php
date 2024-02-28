<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Controller\Meal;

use Mealmatch\ApiBundle\Controller\ApiController;
use Mealmatch\ApiBundle\Entity\Meal\MealJoinRequest;
use Mealmatch\ApiBundle\SymfonyConstants;
use MMApiBundle\Entity\JoinRequest;
use MMApiBundle\Entity\Meal;
use MMApiBundle\MealMatch\FlashTypes;
use MMUserBundle\Entity\MMUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The API MealController is responsible for all user meal interactions. (e.g. after login)
 * NOTE: It delgates "My Meals" to the specific MealType-Controller!
 *
 *
 * @Route("/api/meal")
 * @Security("has_role('ROLE_USER')")
 */
class MealController extends ApiController
{
    /**
     * Forwards to HomeMeal:index or ProMeal:index depending on USER ROLE.
     *
     * @Route("/", name="api_meal_index")
     * @Method("GET")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        // Initialize the ApiController to enable logging
        $this->init();

        // Get the current user for later use
        /** @var MMUser $user */
        $user = $this->getUser();

        // The request prams to forward, specific to meal type
        $proMealReqParams = array(
            SymfonyConstants::SYMF_ROUTE => 'api_promeal_index',
            SymfonyConstants::SYMF_LOCALE => $request->get(SymfonyConstants::SYMF_LOCALE),
            SymfonyConstants::SYMF_ROUTE_PARAMS => $request->get(SymfonyConstants::SYMF_ROUTE_PARAMS),
        );
        $homeMealReqParams = array(
            SymfonyConstants::SYMF_ROUTE => 'api_homemeal_index',
            SymfonyConstants::SYMF_LOCALE => $request->get(SymfonyConstants::SYMF_LOCALE),
            SymfonyConstants::SYMF_ROUTE_PARAMS => $request->get(SymfonyConstants::SYMF_ROUTE_PARAMS),
        );

        // If the USER is a RESTAURANT_USER show ProMealIndex
        if ($user->hasRole('ROLE_RESTAURANT_USER')) {
            // ProMealIndex
            $this->logger->debug(sprintf('->%s forwarding to api_promeal_index.', __METHOD__));

            return $this->forward(
                'ApiBundle:Meal\ProMeal:index', $proMealReqParams);
        }
        // else ... we assume HomeMealIndex
        $this->logger->debug(sprintf('->%s forwarding to api_homemeal_index.', __METHOD__));

        return $this->forward('ApiBundle:Meal\HomeMeal:index', $homeMealReqParams);
    }

    /**
     * @param $receiver
     * @param $sender
     * @param $subject
     * @param $body
     */
    private function sendSystemMessage($receiver, $sender, $subject, $body): void
    {
        $message = $this->get('fos_message.composer')->newThread()
            ->addRecipient($receiver)
            ->setSubject($subject)
            ->setSender($sender)
            ->setBody($body)
            ->getMessage();

        $this->get('fos_message.sender')->send($message);
    }
}

///*
// * Copyright (c) 2016-2017. Mealmatch GmbH
// * (c) André Anneck <andre.anneck@mealmatch.de>
// * Mealmatch WebApp v0.2
// */
//
//namespace MMApiBundle\Controller;
//
//use Doctrine\Common\Collections\Collection;
//use Mealmatch\ApiBundle\Entity\Meal\BaseMeal;
//use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
//use Mealmatch\ApiBundle\Entity\Meal\HomeMeal;
//use Mealmatch\ApiBundle\Entity\Meal\MealJoinRequest;
//use Mealmatch\GameLogicBundle\Core\Score;
//use Mealmatch\GameLogicBundle\Core\UserScore;
//use Mealmatch\GameLogicBundle\Event\Scored;
//use Mealmatch\PayPalBundle\Entity\PayPalPaymentToken;
//use Mealmatch\PayPalBundle\Exceptions\PayPalException as MealmatchPayPalException;
//use Mealmatch\PayPalBundle\Services\PaymentTokenService;
//use Mealmatch\PayPalBundle\Services\PayPalManagerService;
//use MMApiBundle\Entity\Meal;
//use MMApiBundle\Entity\MealTicket;
//use MMApiBundle\MealMatch\FlashTypes;
//use MMApiBundle\MealMatch\Traits\Referer;
//use MMUserBundle\Entity\MMUser;
//use OpenBuildings\PayPal\Exception as PayPalException;
//use OpenBuildings\PayPal\Payment_Adaptive_Chained;
//use OpenBuildings\PayPal\Payment_Adaptive_Simple;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\Translation\IdentityTranslator;
//
///**
// * Meal controller.
// *
// * @deprecated slowly all functionality should move into src/apibundle/
// *
// * @Route("meal")
// * @Security("has_role('ROLE_USER')")
// */
//class MealController extends Controller
//{
//    /*
//     * Traits
//     */
//    use Referer;
//    const PAY_PAL_EXCEPTION = 'PayPalException';
//    const PAYMENT_RESULT = 'Payment Result';
//    const USING_SERVICE_CLASS = 'Using Service Class';
//    const MEAL_ENTITY_CLASS = 'MMApiBundle:Meal';
//
//    /**
//     * The translator used ...
//     *
//     * @var object|IdentityTranslator
//     */
//    private $translator;
//
//    /**
//     * The User calling this action is adding itself as a guest.
//     *
//     * Depending on the meal, the user has to go through a payment process.
//     *
//     * @Route("/{id}/addGuest", name="meal_add_guest", requirements={"id" = "\d+"}))
//     * @Route("/{id}/addGuest/{number}",
//     *     name="meal_add_guests",
//     *     requirements={"id" = "\d+", "number" = "\d+"},
//     *     defaults={"number" = 1}
//     *     )
//     * )
//     * @Method({"POST","GET"})
//     */
//    public function addGuestAction(Request $request, int $numberOfGuests = 1)
//    {
//        // Initialize self ...
//        $this->init();
//
//        $id = $request->get('id');
//        $this->em = $this->getDoctrine()->getManager();
//
//        /** @var BaseMeal $meal */
//        $meal = $this->em->getRepository('ApiBundle:Meal\BaseMeal')->find($id);
//
//        // Host can not join his meals ...
//        if ($this->getUser()->getId() === $meal->getHost()->getId()) {
//            return $this->redirectToRoute('home');
//        }
//        // Have we already joined?
//        /** @var MMUser $user */
//        $user = $this->getUser();
//        if ($user->getAttendingBaseMeals()->contains($meal)) {
//            $noticeToUser = $this->getTranslation('meal.already.requested.to.join');
//            $this->addFlash(
//                FlashTypes::$WARNING,
//                $noticeToUser
//            );
//
//            return $this->redirectToRoute('home');
//        }
//
//        // A "Ticket" to sell for the Meal ...
//        // Find already existing MealTicket for User and Meal
//        /** @var Collection $mealTicketsFound */
//        $mealTicketsFound = $this->get('mm.mealticket')->findByMealAndUser($meal, $user);
//
//        if ($mealTicketsFound->count() > 0) {
//            $mealTicket = $mealTicketsFound->getValues()[0];
//        } else {
//            $mealTicket = $this->createMealTicket($meal);
//        }
//
//        // Meal-Join is for free
//        // ATTENTION!!! getSharedCost is a FLOAT!!!!
//        if (0.0 === $meal->getSharedCost()) {
//            // find joinReq for user using the joinReq from Meal.
//            $jReqs = $meal->getJoinRequests();
//
//            /** @var MealJoinRequest $joinReq */
//            foreach ($jReqs as $joinReq) {
//                if ($joinReq->getCreatedBy() === $this->getUser()
//                    &&
//                    $joinReq->isAccepted()
//                ) {
//                    $joinReq->setPayed(true);
//                    $this->get('logger')->addError('OLD MEAL CONTROLLER ADDING GUEST!!!!');
//                    $meal->addGuest($joinReq->getCreatedBy());
//                    $this->em->persist($joinReq);
//                    $this->em->persist($meal);
//                    $this->em->flush();
//                }
//            }
//            // Create a score for it ...
//            $scoredEvent = new Scored(new UserScore($user, 1, 'MealJoined', Score::COUNTER_TYPE));
//            $this->get('event_dispatcher')->dispatch(Scored::USER, $scoredEvent);
//
//            // Back to JoinRequests
//            return $this->redirectToRoute('joinrequest_index');
//        }
//
//        // SharedCost > 0 ... we need paypal
//        $payToken = $this->doPayment($mealTicket);
//        $redirectUrl = Payment_Adaptive_Simple::approve_url($payToken->getTokenKey());
//
//        // Will result in onCancle or onSuccess ...
//        return $this->redirect($redirectUrl);
//    }
//
//    /**
//     * Deletes a meal entity.
//     *
//     * @Route("/{id}", name="meal_delete")
//     * @Method({"DELETE", "POST"})
//     *
//     * @param Request $request
//     * @param Meal    $meal
//     *
//     * @return \Symfony\Component\HttpFoundation\RedirectResponse
//     */
//    public function deleteAction(Request $request, Meal $meal)
//    {
//        if ($meal->getStatus() === Meal::$STATUS_RUNNING
//            && $meal->getGuests()->count() > 0
//        ) {
//            $this->addFlash(
//                FlashTypes::$WARNING,
//                'Du kannst ein laufendes Meal mit Gästen nicht mehr löschen.'
//            );
//
//            return $this->redirectToRoute('meal_index');
//        }
//        $form = $this->createDeleteForm($meal);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $this->em = $this->getDoctrine()->getManager();
//
//            try {
//                $meal->setStatus(Meal::$STATUS_DELETED);
//                $this->em->persist($meal);
//                $this->em->flush();
//                $this->addFlash(FlashTypes::$SUCCESS, 'Removed!');
//            } catch (\Exception $exception) {
//                $this->addFlash('danger', 'Not Removed!'.$exception->getMessage());
//            }
//        }
//
//        return $this->redirectToRoute('meal_index');
//    }
//
//    /**
//     * Stoppes a meal entity.
//     *
//     * @Route("/{id}/stop", name="meal_stop")
//     * @Method("GET")
//     */
//    public function stopOfferAction(Request $request, Meal $meal)
//    {
//        // Initialize self ...
//        $this->init();
//
//        // @todo: Missing translation!
//        if ($meal->getStatus() === Meal::$STATUS_RUNNING
//            && $meal->getGuests()->count() > 0
//        ) {
//            $this->addFlash(
//                FlashTypes::$WARNING,
//                'Du kannst ein laufendes Meal mit Gästen nicht mehr beenden.'
//            );
//
//            return $this->redirectToRoute('meal_index');
//        }
//
//        $meal->setStatus(Meal::$STATUS_STOPPED);
//
//        $this->em = $this->getDoctrine()->getManager();
//        $this->em->persist($meal);
//        $this->em->flush();
//        $this->addFlash(
//            FlashTypes::$SUCCESS,
//            'Dein Meeal wurde erfolgreich gestoppt!'
//        );
//
//        return $this->redirectToRoute('meal_index');
//    }
//
//    /**
//     * MealController constructor.
//     */
//    private function init()
//    {
//        $this->translator = $this->get('translator');
//    }
//
//    /**
//     * @param        $meal
//     * @param        $this->em
//     * @param string $myArgument with a *description* of this argument, these may also
//     *                           span multiple lines
//     *
//     * @return BaseMealTicket
//     * @todo: Finish PHPDoc!
//     * A summary informing the user what the associated element does.
//     *
//     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
//     * and to provide some background information or textual references.
//     */
//    private function createMealTicket(BaseMeal $meal): BaseMealTicket
//    {
//        // Initialize self ...
//        $this->init();
//
//        $this->em = $this->getDoctrine()->getManager();
//
//        $mealTicket = new BaseMealTicket();
//        $mealTicket->setHost($meal->getHost());
//        $mealTicket->setGuest($this->getUser());
//        $mealTicket->setBaseMeal($meal);
//        $mealTicket->setPrice($meal->getSharedCost());
//        $mealTicket->setCurrency($meal->getSharedCostCurrency());
//        $mealTicket->setTitel($meal->getTitle());
//        $description = '-/-';
//        if ($meal instanceof HomeMeal) {
//            $description = 'Mealmatch Menu: '
//                .$meal->getMealStarter().'~'
//                .$meal->getMealMain().'~'
//                .$meal->getMealDesert().'.';
//        }
//        $mealTicket->setDescription($description);
//        $mmFee = $meal->getSharedCost() * 0.085;
//
//        $mealTicket->setMmFee($mmFee);
//        $this->em->persist($mealTicket);
//        $this->em->flush();
//
//        $mmNumberTmp = array($meal->getId(), $meal->getHost()->getId(), $mealTicket->getId());
//        $mmNumber = '#MM#'.implode('-', $mmNumberTmp);
//        $mealTicket->setNumber($mmNumber);
//
//        $this->em->persist($mealTicket);
//        $this->em->persist($meal);
//        $this->em->flush();
//
//        return $mealTicket;
//    }
//
//    /**
//     * @param        $mealTicket
//     * @param        $instances
//     * @param        $logger
//     * @param        $this->em
//     * @param string $myArgument with a *description* of this argument, these may also
//     *                           span multiple lines
//     *
//     * @throws MealmatchPayPalException
//     *
//     * @return PayPalPaymentToken
//     *
//     * @todo: Finish PHPDoc!
//     * A summary informing the user what the associated element does.
//     *
//     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
//     * and to provide some background information or textual references.
//     */
//    private function doPayment(MealTicket $mealTicket): PayPalPaymentToken
//    {
//        // Initialize self ...
//        $this->init();
//
//        $this->em = $this->getDoctrine()->getManager();
//        $logger = $this->get('logger');
//
//        /** @var PayPalManagerService $payPalManager */
//        $payPalManager = $this->get('mealmatch_paypal.manager');
//
//        /** @var Payment_Adaptive_Chained $adaptiveChained */
//        $adaptiveChained = $payPalManager->getByService('Adaptive_Chained');
//
//        try {
//            $paymentResult = $payPalManager->doPayment($adaptiveChained, $mealTicket);
//        } catch (PayPalException $epp) {
//            $logger->addError('PayPalException: '.$epp->getMessage());
//            $paymentResult = array(
//                static::PAY_PAL_EXCEPTION => $epp->getMessage(),
//            );
//        }
//
//        $result = array(
//            static::USING_SERVICE_CLASS => $adaptiveChained::$instances,
//            static::PAYMENT_RESULT => $paymentResult,
//        );
//
//        /** @var PaymentTokenService $paymentTokenService */
//        $paymentTokenService = $this->get('mealmatch_paypal.payment_token');
//
//        return $paymentTokenService->trackAdaptiveChainedTicketResult($adaptiveChained, $result, $mealTicket);
//    }
//
//    /**
//     * Creates a form to delete a meal entity.
//     *
//     * @param Meal $meal The meal entity
//     *
//     * @return \Symfony\Component\Form\Form The form
//     */
//    private function createDeleteForm(Meal $meal)
//    {
//        return $this->createFormBuilder()
//            ->setAction($this->generateUrl('meal_delete', array('id' => $meal->getId())))
//            ->setMethod('DELETE')
//            ->getForm();
//    }
//
//
//    private function getTranslation(string $id, array $options = array())
//    {
//        return $this->translator->trans(
//            $id,
//            $options,
//            'Mealmatch'
//        );
//    }
//}
