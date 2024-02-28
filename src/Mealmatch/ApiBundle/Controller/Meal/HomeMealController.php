<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Controller\Meal;

use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Controller\ApiController;
use Mealmatch\ApiBundle\Entity\Meal\HomeMeal;
use Mealmatch\ApiBundle\Entity\Meal\MealPart;
use Mealmatch\ApiBundle\MealMatch\FlashTypes;
use Mealmatch\ApiBundle\Services\HomeMealService;
use MMUserBundle\Entity\MMUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * Homemeal controller.
 *
 * @Route("api/homemeal")
 * @Security("has_role('ROLE_USER')")
 */
class HomeMealController extends ApiController
{
    /**
     * Lists all proMeal entities.
     *
     * @Route("/", name="api_homemeal_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $this->init();
        $this->logger->debug(sprintf('->%s', __METHOD__));

        $viewData = $this->createAllHomeMealViewData();

        return $this->render('@WEBUI/HomeMeal/index.html.twig', $viewData);
    }

    /**
     * Creates a new HomeMeal or shows it. There is always only 1 Meal in this condition per user.
     *
     * @Route("/new", name="api_homemeal_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        /** @var MMUser $host */
        $host = $this->getUser();

        $canReceivePayment =
            $this->get('Mealmatch\MangopayBundle\Services\PublicMangopayService')->validateUserCanReceivePayin($host);

        // Validate if Host can receive payments
        if (!$canReceivePayment) {
            $this->addFlash(FlashTypes::$DANGER, 'Host payment validation failed!');

            return $this->redirectToRoute('api_homehost_profile_manager');
        }

        $homeMealService = $this->get('api.home_meal.service');

        $homeMeal = $this->getLastOrNewHomeMeal($homeMealService);

        return $this->redirectToRoute('api_homemeal_manager_edit', array('id' => $homeMeal->getId()));
    }

    /**
     * Private helper to extract the last edited ProMeal, using the session or create a brand new ProMeal.
     *
     * @param HomeMealService $homeMealService the service to use
     *
     * @return HomeMeal the only CREATED HomeMeal or a new one
     */
    private function getLastOrNewHomeMeal(HomeMealService $homeMealService): HomeMeal
    {
        $this->init();
        // there should always only be 1 ...
        $newlyCreated = $homeMealService->findCreatedByOwnerAsCollection($this->getUser());
        if ($newlyCreated->count() > 0) {
            $this->logger->addDebug('Get first created HomeMeal for user: '.$this->getUser());

            return $newlyCreated->first();
        }

        // There is no CREATED HomeMeal yet!
        // Create a default!
        $homeMeal = new HomeMeal();
        $homeMeal->setTitle('New ...');
        $homeMeal->setDescription(' ... ');
        $homeMeal->setMealMain('Nudeln...');
        $homeMeal->setStatus(ApiConstants::MEAL_STATUS_CREATED);

        // MealParts 1,2 and 3.
        $mealPart1 = (new MealPart())->setName('Vorspeise')->setDescription('...z.B. Suppe!');
        $mealPart2 = (new MealPart())->setName('Hauptgang')->setDescription('...z.B. Nudeln!');
        $mealPart3 = (new MealPart())->setName('Nachtisch')->setDescription('...z.B. Eiscreme!');
        $homeMeal->addMealPart($mealPart1);
        $homeMeal->addMealPart($mealPart2);
        $homeMeal->addMealPart($mealPart3);

        $createdHomeMeal = $homeMealService->createFromEntityWithHost($homeMeal, $this->getUser())->getHomeMeal();

        $this->logger->addInfo('Created new HomeMeal: '.$createdHomeMeal);

        return $createdHomeMeal;
    }
}
