<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\WorkflowBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use MangoPay\Transfer;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Entity\Meal\BaseMeal;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
use Mealmatch\ApiBundle\Exceptions\MealmatchException;
use Mealmatch\ApiBundle\MealMatch\Traits\Referer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Workflow\Exception\LogicException;

/**
 * The transition controller is executing workflow transitions on subjects.
 *
 * @Route("api/workflow")
 * @Security("has_role('ROLE_USER')")
 */
class TransitionController extends Controller
{
    use Referer;

    /**
     * The Transition action executes BaseMeal transition.
     *
     * @Route("/doTransition/Meal/{id}/{transition}", name="doTransitionBaseMeal")
     *
     * @param Request  $request    the HTTP Request to process
     * @param BaseMeal $meal
     * @param string   $transition
     *
     * @return RedirectResponse
     */
    public function transitionBaseMealAction(Request $request, BaseMeal $meal, string $transition): RedirectResponse
    {
        // Null check for $meal parameter is within the "logic" as the ID could have been deleted from the DB.
        if (null === $meal) {
            $id = $request->get('id');
            $msg = $this->get('translator')->trans('meal.not_found', array('%mealid%' => $id), 'Mealmatch');
            $this->addFlash('info', $msg);

            return $this->redirectToRoute('api_meal_index');
        }

        // try to apply transition ...
        // AND RETURN, END the execution of this method.
        try {
            $this->get('workflow.base_meal')->apply($meal, $transition);
            // worked! ... now return based on transition ...
            switch ($transition) {
                case 'join_meal':
                    if ('HomeMeal' === $meal->getMealType()) {
                        return $this->redirectToRoute('meal_add_join_request', array('id' => $meal->getId()));
                    }

                    return $this->redirectToRoute(
                        $this->getRefererParams($request)['_route'],
                        $this->getRefererParams($request));

                    break;
                default:
                    return $this->redirectToRoute('api_meal_index');
                    break;
            }
        } catch (LogicException $logicException) {
            // Failed to apply transition triggers a WARNING for the user to enable retries.
            $this->get('logger')->addError($logicException->getMessage());
            $this->addFlash('warning', $logicException->getMessage());
        }
        // Something went wrong ...
        // The method should have ended within the try block.
        // Gather parameters from request to redirect back to where the user started the transition request.
        // $params = $this->getRefererParams($request);
        // Return the user with the mealID he specified to its origin request, gathered from the '_route' request.
        return $this->redirect($this->generateUrl(
            $this->getRefererParams($request)['_route'],
            array(
                'id' => $meal->getId(),
            )
        ));
    }

    /**
     * The Transition action executes MealTicket transition.
     *
     * @Route(
     *     "/doTransition/Ticket/{id}/{transition}/{paymentType}",
     *     name="doTransitionMealTicket",
     *     defaults={"paymentType": "CARD"}
     *     )
     *
     * @param Request        $request    the HTTP Request to process
     * @param BaseMealTicket $mealTicket
     * @param string         $transition
     *
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @return RedirectResponse
     */
    public function transitionMealTicketAction(Request $request, BaseMealTicket $mealTicket, string $transition): RedirectResponse
    {
        $this->get('logger')->debug('>>>> Transition Mealticket Action');
        // Verify that the meal exists ...
        if (null === $mealTicket) {
            $id = $request->get('id');
            $msg = $this->get('translator')->trans('mealticket.not_found', array('%id%' => $id), 'Mealmatch');
            $this->addFlash('info', $msg);

            return $this->redirectToRoute('api_meal_index');
        }
        $this->get('logger')->debug('>>>> Transition Mealticket Action: meal exists');
        $orm = $this->get('doctrine.orm.default_entity_manager');
        // Hack for 0 € Meals dont need payment.
        if ($mealTicket->getPrice() < 1) {
            $mealTicket->setStatus(ApiConstants::MEAL_TICKET_STATUS_PAYED);
            $meal = $mealTicket->getBaseMeal();
            $mealTicket->getBaseMeal()->addGuest($mealTicket->getGuest());
            /* @var EntityManager $orm */
            $orm->persist($meal);
            $orm->persist($mealTicket);
            $orm->flush();
            $this->get('logger')->debug('>>>> Transition Mealticket Action: 0 meal hack');

            return $this->redirectToRoute('api_mealticket_show', array('id' => $mealTicket->getId()));
        }

        // Special Hack for 0€ after Coupon as been factored in
        if (0 === $mealTicket->getTotalPriceInCent()) {
            if (null !== $mealTicket->getCoupon()) {
                try {
                    /** @var Transfer $transfer */
                    $transfer = $this->get('PublicMangopayService')->createTransferCouponToGuestWallet($mealTicket);

                    $transferResult = $this->get('PublicMangopayService')->executeTransfer($transfer);

                    $this->get('MealticketTransactionService')->createFromTransfer($mealTicket, $transferResult);
                } catch (MealmatchException $mealmatchException) {
                    $this->get('logger')->error('paySuccess()--->Failed to createMTT: '.
                        $mealmatchException->getMessage());
                } catch (\Exception $exception) {
                    $this->get('logger')->error('paySuccess()--->Failed to createMTT: '.
                        $exception->getMessage());
                }

                if (ApiConstants::TRANSACTION_STATUS_FAILED === $transferResult->Status) {
                    $this->get('logger')->error('paySuccess()--->Failed to execute Coupon Transfer: '.
                        $transferResult->ResultCode.'#'.$transferResult->ResultMessage);
                }
            }

            $meal = $mealTicket->getBaseMeal();
            $mealTicket->getBaseMeal()->addGuest($mealTicket->getGuest());
            $mealTicket->setStatus(ApiConstants::MEAL_TICKET_STATUS_PAYED);
            /* @var EntityManager $orm */
            $orm->persist($meal);
            $orm->persist($mealTicket);
            $orm->flush();

            return $this->redirectToRoute('api_mealticket_show', array('id' => $mealTicket->getId()));
        }

        // try to apply transition ...
        try {
            $this->get('logger')->debug('>>>> Transition Apply '.$transition);

            if ($this->get('workflow.meal_ticket')->can($mealTicket, $transition)) {
                $this->get('workflow.meal_ticket')->apply($mealTicket, $transition);
                // worked! ... now return based on transition ...
                if ('pay_ticket' === $transition && null !== $mealTicket->getRedirectURL()) {
                    //MangoPay Url Redirection
                    return $this->redirect($mealTicket->getRedirectURL());
                }
            } else {
                $this->get('logger')->addWarning('Could not apply transition: '.$transition);
                $this->addFlash('warning', 'Could not apply transition: '.$transition);
            }
        } catch (LogicException $logicException) {
            $this->get('logger')->addError($logicException->getMessage());
            $this->addFlash('warning', $logicException->getMessage());
        }
        // Return to Ticket
        return $this->redirectToRoute('api_mealticket_show', array('id' => $mealTicket->getId()));
    }
}
