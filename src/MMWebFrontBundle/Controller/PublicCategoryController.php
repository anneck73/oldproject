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

/**
 * /p/social-dining/{category}-essen
 * /p/social-dining/{category}-essen/in-{city}
 * /p/social-dining/{category}-essen/heute-in-{city}.
 */
class PublicCategoryController extends ApiController
{
    /**
     * Displays all Meals matching the category.
     *
     * @Route("p/social-dining/{cat}-essen",
     *     name="public_meals_category_essen",
     *     requirements={"cat": "vegan|vegetarisch|fischgericht|fleischgericht|laktosefrei"}
     *  )
     * @Method("GET")
     */
    public function showMealsByCategoryAction(string $cat)
    {
        $catMeals = $this->get('api.meal.service')->findRunningByCategory($cat);

        return $this->render(
            '@WEBUI/Meals/meals.category.index.html.twig',
            array(
                'category' => $cat,
                'allMeals' => $catMeals,
            )
        );
    }
}
