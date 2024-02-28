<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMWebFrontBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Mealmatch\ApiBundle\Controller\ApiController;
use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Mealmatch\ApiBundle\MealMatch\CollectionHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * This ProMealController serves public routes and enriches the pages with SEO data.
 * It Re-Directs from specific URL's to have unique resources and ensures
 * meta-data REL-.
 */
class PublicProMealController extends ApiController
{
    /**
     * Displays the unique ProMeal or 404.
     *
     * @Route("p/restaurant-meal/{hostName}/{mealTitle}/{mealID}",
     *     name="public_promeal_hostname_mealtitle_id")
     * @Method("GET")
     */
    public function showProMealsByHostTitleIDAction(string $hostName, string $mealTitle, int $mealID)
    {
        if (null === $mealTitle) {
            throw new NotFoundHttpException('Mealtitle not set!');
        }
        if (null === $hostName) {
            throw new NotFoundHttpException('Hostname not set!');
        }
        if (null === $mealID) {
            throw new NotFoundHttpException('MealID not set!');
        }
        // get user and meal
        $user = $this->get('api.user_manager')->findUserByUsername($hostName);
        $meal = $this->get('api.pro_meal.service')->restore($mealID)->getProMeal();
        if (null === $user || null === $meal) {
            throw new NotFoundHttpException('No Meal found!');
        }

        $this->get('api.seo')->enrichSEOWithProMeal($meal);

        return $this->render(
            '@WEBUI/ProMeal/publicMealDetails.html.twig',
            array(
                'hostName' => $hostName,
                'mealTitle' => $mealTitle,
                'proMeal' => $meal,
            )
        );
    }

    /**
     * Shows all ProMeals for Hostname/Titel.
     *
     * @Route("p/restaurant-meal/{hostName}/{mealTitle}",
     *     name="public_promeal_hostname_mealtitle")
     * @Method("GET")
     */
    public function showProMealsByHostTitleAction(string $hostName, string $mealTitle)
    {
        if (null === $mealTitle) {
            throw new NotFoundHttpException('Mealtitle not set!');
        }
        if (null === $hostName) {
            throw new NotFoundHttpException('Hostname not set!');
        }
        // get user and meal
        $user = $this->get('api.user_manager')->findUserByUsername($hostName);
        $meals = $this->get('api.pro_meal.service')->findByTitle($mealTitle);

        if (null === $user || 0 === \count($meals)) {
            throw new NotFoundHttpException('No Meals by title not found!');
        }

        $mealsSorted = CollectionHelper::sortByStartDate(new ArrayCollection($meals));

        // Enrich this call with SEO
        $this->get('api.seo')->enrichSEO();

        return $this->render(
            '@WEBUI/ProMeal/meals.host.index.html.twig',
            array(
                'proMeals' => $mealsSorted->toArray(),
                'hostName' => $hostName,
                'mealTitle' => $mealTitle,
            )
        );
    }

    /**
     * Restaurant-Meal by ID, redirects to public_promeal_hostname_mealtitle_id.
     *
     * @Route("p/restaurant-meal/{id}", name="public_promeal_show")
     * @Method("GET")
     */
    public function showProMealAction(ProMeal $proMeal)
    {
        return $this->redirectToRoute('public_promeal_hostname_mealtitle_id', array(
                'mealTitle' => $proMeal->getTitle(),
                'hostName' => $proMeal->getHost()->getUsername(),
                'mealID' => $proMeal->getId(),
            )
        );
    }

    /**
     * All Restaurant-Meals.
     *
     * @Route("p/social-dining/restaurant/", name="public_promeal_index")
     * @Method("GET")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showProMealsIndexAction()
    {
        // Enrich this call with SEO
        $this->get('api.seo')->enrichSEO();
        // Get all HomeMeals ...
        $allProMeals = $this->get('api.pro_meal.service')->findAll();
        // Sort by start date ...
        $proMealsSorted = CollectionHelper::sortByStartDate(new ArrayCollection($allProMeals));

        return $this->render(
            '@WEBUI/ProMeal/meals.index.html.twig',
            array(
                'proMeals' => $proMealsSorted->toArray(),
            )
        );
    }

    /**
     * Home-Meals of a Host.
     *
     * @Route("p/social-dining/restaurant/{hostName}",
     *     name="public_promeal_hostname")
     * @Method("GET")
     */
    public function showProMealsOfHostAction(string $hostName = null)
    {
        $user = $this->get('api.user_manager')->findUserByUsername($hostName);

        if (null === $user) {
            throw new NotFoundHttpException('Unknown Host! Hostname: '.$hostName);
        }
        // Get all HomeMeals ...
        $allHostMeals = $this->get('api.pro_meal.service')->findAllByOwner($user);

        // only starting today or in the future ...
        $allHostMealsFiltered = CollectionHelper::filterBaseMealsEqualOrAfterStart(
            new ArrayCollection($allHostMeals),
            new \DateTime('today'))->toArray();

        // Sort by start date ...
        $mealsSorted = CollectionHelper::sortByStartDate(new ArrayCollection($allHostMealsFiltered));

        return $this->render(
            '@MMWebFront/ProMeal/meals.host.index.html.twig',
            array(
                'proMeals' => $mealsSorted->toArray(),
                'hostName' => $hostName,
            )
        );
    }

    /**
     * Show Restaurant-Meal by hash (unique).
     *
     * @Route("p/restaurant-meal/mm_{hash}", name="public_promeal_byhash")
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function showProMealByHashAction(Request $request, string $hash)
    {
    }

    /**
     * Shows ProMeals on the given Date.
     *
     * @Route("p/social-dining/restaurant/{restaurantName}/{tableTopic}/{startDate}", name="public_promeal_r_t_d")
     * @Method("GET")
     */
    public function showProMealByRestaurantTabletopicStartDateAction(
        string $tableTopic = null,
        string $restaurantName = null,
        string $startDate = null
    ) {
        $proMeals = $this->get('api.pro_meal.service')->findOneByRTD(
            $restaurantName,
            $tableTopic,
            $startDate
        );
        /** @var ProMeal $proMeal */
        $proMeal = $proMeals[0];

        return $this->forward('MMWebFrontBundle:PublicProMeal:show', array('id' => $proMeal->getId()));
    }

    /**
     * Shows all proMeals with the same TableTopic.
     *
     * @Route("p/social-dining/restaurants/{tableTopic}", name="public_restaurant_tabletopic")
     * @Method("GET")
     */
    public function showByTableTopicAction(string $tableTopic = null)
    {
        $proMeals = $this->get('api.pro_meal.service')->findAllByTableTopic($tableTopic);

        $viewData = array(
            'tableTopicMeals' => $proMeals,
            'tableTopic' => $tableTopic,
            'title' => $this->trans('public.tabletopic.meals.title'),
        );

        return $this->render(
            '@WEBUI/TableTopic/meals.index.html.twig',
            $viewData
        );
    }

    /**
     * Finds and displays a proMeal entity MODAL.
     *
     * @Route("p/social-dining/restaurant/modal/{id}", name="public_promeal_modal")
     * @Method("GET")
     */
    public function modalAction(ProMeal $proMeal)
    {
        $availableDates = $this->get('api.meal_event.service')->getAvailableDatesForProMeal($proMeal);

        return $this->render(
            '@WEBUI/ProMeal/Card/container.html5.twig',
            array(
                'proMeal' => $proMeal,
                'availableDates' => $availableDates,
                'rProfile' => $proMeal->getHost()->getRestaurantProfile(),
                '_locale' => 'de',
            )
        );
    }
}
