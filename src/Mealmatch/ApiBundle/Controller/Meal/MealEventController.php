<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Controller\Meal;

use Mealmatch\ApiBundle\Controller\ApiController;
use Mealmatch\ApiBundle\Entity\Meal\MealEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class MealOfferController does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 *
 * @Route("api/mealevent")
 */
class MealEventController extends ApiController
{
    /**
     * Updates MealEvent and returns to api_promeal_manager_show.
     *
     * @Route("/{id}/update", name="api_mealevent_update")
     * @Method({"POST"})
     */
    public function updateEventAction(Request $request, MealEvent $mealEvent)
    {
        $updateForm = $this->createForm('Mealmatch\ApiBundle\Form\Meal\MealEventType', $mealEvent);
        $updateForm->handleRequest($request);

        if ($updateForm->isSubmitted() && $updateForm->isValid()) {
            $this->getDoctrine()->getManager()->persist($mealEvent);
            $this->getDoctrine()->getManager()->flush();
        }

        $mealID = $request->get('mealID');

        $redirectTarget = $this->getRedirectTargetByMealType($mealID);

        return $this->redirectToRoute($redirectTarget, array('id' => $mealID, 'selectedTab' => '4'));
    }

    /**
     * Removes the specified mealEvent by ID and redirects to ProMeal/HomeMeal-ManagerController.
     *
     * @Route("/{id}/remove", name="api_mealevent_remove")
     * @Method({"POST", "GET"})
     */
    public function removeEventAction(Request $request, MealEvent $mealEvent)
    {
        $mealID = $request->get('mealID');

        $this->get('api.meal.service')->removeEventFromMeal($mealID, $mealEvent->getId());

        $redirectTarget = $this->getRedirectTargetByMealType($mealID);

        return $this->redirectToRoute($redirectTarget, array('id' => $mealID, 'selectedTab' => '4'));
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param        $mealID
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return string
     */
    private function getRedirectTargetByMealType($mealID): string
    {
        $mealType = $this->get('api.meal.service')->getMealType($mealID);
        $redirectTarget = 'api_homemeal_manager_edit';
        if ('ProMeal' === $mealType) {
            $redirectTarget = 'api_promeal_manager_edit';
        }

        return $redirectTarget;
    }
}
