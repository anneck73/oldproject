<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMWebFrontBundle\Controller;

use Mealmatch\ApiBundle\Controller\ApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * The Public City Controller shows Meals by City.
 *
 * @Route("/restaurants")
 */
class RestaurantsController extends ApiController
{
    /**
     * Shows all meals running in the city specified.
     * !Only Köln, Essen, Münster are currently used!
     *
     * Todo: Check if city exists ... allow all kind of city names.
     *
     * @Route("/{city}", name="restaurants_city",
     *
     *      requirements={"city": "Köln|Essen|Münster"}
     *  )
     * @Method("GET")
     */
    public function showRestaurantsByCityAction(Request $request, string $city = null)
    {
        $cityRestaurants = $this->get('api.restaurant.service')->findAllWithRunningMeal(
            array(
                'city' => $city,
            )
        );

        $viewData = array(
            'restaurantMeals' => $cityRestaurants,
            'city' => $city,
            'title' => $this->trans('public.city.restaurants.title'),
        );

        return $this->render(
            '@WEBUI/City/restaurants.index.html.twig',
            $viewData
        );
    }
}
