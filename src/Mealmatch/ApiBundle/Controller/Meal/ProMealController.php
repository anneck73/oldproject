<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Controller\Meal;

use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Controller\ApiController;
use Mealmatch\ApiBundle\Entity\Meal\MealOffer;
use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Mealmatch\ApiBundle\Services\ProMealService;
use Mealmatch\ApiBundle\Services\RestaurantService;
use MMUserBundle\Entity\MMUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This ProMeal controller work together with the ProMealManagerController and deals with
 * the creation of ProMeal.
 *
 * @Route("api/promeal")
 * @Security("has_role('ROLE_RESTAURANT_USER')")
 */
class ProMealController extends ApiController
{
    /**
     * Lists all proMeal entities.
     *
     * @Route("/", name="api_promeal_index")
     * @Method("GET")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        $this->init();
        $this->logger->debug(sprintf('->%s', __METHOD__));

        // ?sortby=status,sortorder=natural,filterby=status,filtervalue=aktiv
        $requestParameters = $request->query->all();

        $sortOptions = array(
            'by' => $requestParameters['sortBy'] ?? 'natural',
            'order' => $requestParameters['sortOrder'] ?? 'desc',
        );

        $filterOptions = array(
            'by' => $requestParameters['filterBy'] ?? 'status',
            'value' => $requestParameters['filterValue'] ?? 'active',
        );
        $searchOptions = array(
          $sortOptions, $filterOptions,
        );

        $viewData = $this->createAllProMealViewData($searchOptions);

        return $this->render('@WEBUI/ProMeal/index.html.twig', $viewData);
    }

    /**
     * Creates a new proMeal entity (step 1, only ProMealType) and redirects to promeal_manager.
     *
     * NOTE: The process is only triggered if the RestaurantProfile of the current user is valid! If not the
     * user is redirected to the restaurant profile and a toaster message appears to inform the user that
     * his profile is not "valid for the creation of ProMeals" yet.
     *
     * @Route("/new", name="api_promeal_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        /** @var RestaurantService $restaurantServcie */
        $restaurantServcie = $this->get('api.restaurant.service');

        /** @var MMUser $host */
        $host = $this->getUser();
        // A ProMeal requires mangoay ids...
        $hasMangopayIds = $restaurantServcie->hasRestaurantProfileNeededMangopayIds($host->getPaymentProfile());
        if (!$hasMangopayIds) {
            // MangopayIds MISSING, notify with toaster
            $this->addFlash('danger',
                $this->trans('restaurantprofile.paymentprofile.validation.nok')
            );

            return $this->redirectToRoute('api_restaurant_profile_manager');
        }

        // RestaurantProfile is VALID, continue ...
        // The first time, we create a new Root-ProMeal. Every consecutive time we take the existing one.
        $proMeal = $this->getLastOrNewProMeal();

        // Then redirect to ProMealManager:edit ...
        return $this->redirectToRoute('api_promeal_manager_edit', array('id' => $proMeal->getId()));
    }

    /**
     * Join a ProMeal.
     *
     * @Route("/{proMeal}/join/{mealOffer}", name="api_promeal_join")
     * @Method({"GET", "POST"})
     * @ParamConverter(name="proMeal", class="ApiBundle:Meal\ProMeal")
     * @ParamConverter(name="mealOffer", class="ApiBundle:Meal\MealOffer")
     * @Security("has_role('ROLE_USER')")
     */
    public function joinAction(ProMeal $proMeal = null, MealOffer $mealOffer = null)
    {
        /** @var MMUser $user */
        $user = $this->getUser();

        // Find the last or create a new Ticket ...
        $proMealTicket = $this->get('api.meal_ticket.service')
            ->findOrCreateNewFromProMeal($proMeal, $mealOffer, $user);

        // Try to pay with this ticket (will fail if new anyway) ...
        $resultSD = $this->get('api.pro_meal.service')->joinMeal($proMealTicket);

        // the good ... @todo
        if ($resultSD->isValid()) {
            // Show ProMeal, now with more guests...
            return $this->redirectToRoute('public_promeal_show', array('id' => $proMeal->getId()));
        }

        // the new Ticket ...
        return $this->redirectToRoute('api_mealticket_show', array('id' => $proMealTicket->getId()));
    }

    /**
     * Finds and displays a proMeal entity.
     *
     * @Route("/{id}", name="api_promeal_show")
     * @Method("GET")
     */
    public function showAction(ProMeal $proMeal)
    {
        $availableDates = $this->get('api.meal_event.service')->getAvailableDatesForProMeal($proMeal);

        return $this->render(
            '@WEBUI/ProMeal/Card/promeal.html5.twig',
            array(
                'proMeal' => $proMeal,
                'availableDates' => $availableDates,
                'rProfile' => $proMeal->getHost()->getRestaurantProfile(),
            )
        );
    }

    /**
     * Private helper to extract the last edited ProMeal, using the session or create a brand new ProMeal.
     *
     * @return ProMeal the ProMeal from Session or a new one
     */
    private function getLastOrNewProMeal(): ProMeal
    {
        /** @var ProMealService $proMealService */
        $proMealService = $this->get('api.pro_meal.service');

        // there should always only be 1 ...
        $newlyCreated = $proMealService->findCreatedByOwnerAsCollection($this->getUser());
        if ($newlyCreated->count() > 0) {
            return $newlyCreated->first();
        }

        // There is no CREATED ProMeal yet!
        $defaultTitle = $this->trans('promeal.default.title.text');
        $defaultDescription = $this->trans('promeal.default.description.text');
        // Create a new one using defaults and the restaurant profile address ...
        $proMeal = new ProMeal();
        $proMeal->setTitle($defaultTitle);
        $proMeal->setDescription($defaultDescription);
        $proMeal->setStatus(ApiConstants::MEAL_STATUS_CREATED);

        $serviceResult = $proMealService->createFromEntityWithHost($proMeal, $this->getUser());
        if (!$serviceResult->isValid()) {
            $this->addFlash('danger', $serviceResult->getErrorsAsJSON());
        }

        return $serviceResult->getProMeal();
    }
}
