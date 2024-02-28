<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\RestaurantWebFrontBundle\Controller;

use Mealmatch\ApiBundle\Controller\ApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Default for Domain "mealmatch.restaurant".
 */
class DefaultController extends ApiController
{
    /**
     * @see ISSUE-118 (bitbucket)
     * @see WEBAPP-47
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/", name="restaurantHome", host="www.mealmatch.restaurant")
     * @Route("/", name="restaurantHomeDev", host="restaurant.mealmatch.local")
     */
    public function indexAction(Request $request)
    {
        $referer = $request->headers->get('referer');
        // Redirect everyone NOT from our WebApp to the Restaurant-Registration
        if (!(strpos($referer, 'mealmatch') > 0)) {
            return $this->redirectToRoute('fos_user_registration_register_pro');
        }

        // All meals ...
        $meals = $this->get('api.meal.service')->findAll();

        // Choose a variation and set the template name...
        $variant = $this->determineVariation($request);
        $templateName = '@MealmatchRestaurantWebFront/Variations/Default/'.$variant.'/index.html.twig';

        return $this->render(
            $templateName,
            array(
                'meals' => $meals,
            )
        );
    }
}
