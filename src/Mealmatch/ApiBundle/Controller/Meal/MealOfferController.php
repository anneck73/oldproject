<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Controller\Meal;

use Mealmatch\ApiBundle\Controller\ApiController;
use Mealmatch\ApiBundle\Entity\Meal\MealOffer;
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
 * @Route("api/mealoffer")
 */
class MealOfferController extends ApiController
{
    /**
     * Updates MealOffer and redirects to api_promeal_manager_show.
     *
     * @Route("/{id}/update", name="api_mealoffer_update")
     * @Method({"POST"})
     */
    public function updateOfferAction(Request $request, MealOffer $mealOffer)
    {
        $updateForm = $this->createForm('Mealmatch\ApiBundle\Form\Meal\MealOfferType', $mealOffer);
        $updateForm->handleRequest($request);

        if ($updateForm->isSubmitted() && $updateForm->isValid()) {
            $this->getDoctrine()->getManager()->persist($mealOffer);
            $this->getDoctrine()->getManager()->flush();
        }
        $proMealID = $request->get('proMealID');

        return $this->redirectToRoute('api_promeal_manager_edit',
            array('id' => $proMealID, 'selectedTab' => '2')
        );
    }

    /**
     * Removes the specified mealOffer by ID and redirects to ProMeal-ManagerController.
     *
     * @Route("/{id}/remove", name="api_mealoffer_remove")
     * @Method({"POST", "GET"})
     */
    public function removeOfferAction(Request $request, MealOffer $mealOffer)
    {
        $mealID = $request->get('mealID');

        try {
            $this->get('api.meal.service')->removeOfferFromProMeal($mealID, $mealOffer->getId());
        } catch (ORMException $ORMException) {
            $this->logger->err('Failed to remove Offer from ProMeal: '.$ORMException->getMessage());
        }

        $redirectTarget = 'api_promeal_manager_edit';

        return $this->redirectToRoute($redirectTarget, array('id' => $mealID, 'selectedTab' => '2'));
    }
}
