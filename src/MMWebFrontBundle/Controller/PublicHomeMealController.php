<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMWebFrontBundle\Controller;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Mealmatch\ApiBundle\Controller\ApiController;
use Mealmatch\ApiBundle\Entity\Meal\BaseMeal;
use Mealmatch\ApiBundle\Entity\Meal\HomeMeal;
use Mealmatch\ApiBundle\MealMatch\CollectionHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * All Public HomeMeal specific URLs with SEO.
 *
 * Einzelnen Home-Meal URL's
 * p/home-meal/{hostName}/{mealTitle}/{mealID}
 * p/home-meal/{hostName}/{mealTitle}
 * p/home-meal/{id}
 * p/home-meal/{mm_hash}
 *
 * Listen mit Home-Meals
 * p/social-dining/home
 * p/social-dining/home/{hostName}
 *
 * @todo:
 * p/social-dining/home/heute
 * p/social-dining/home/morgen
 * p/social-dining/home/am-wochenende
 * p/social-dining/home/nächstes-wochenende
 * p/social-dining/home/demnächst
 * p/social-dining/home/in-dieser-woche
 * p/social-dining/home/in-diesem-monat
 *
 * Kalendaransicht mit Home-Meals
 * p/social-dining/home/calendar (kalendar)
 * p/social-dining/home/termine (Zeitband)
 */
class PublicHomeMealController extends ApiController
{
    /**
     * Finds and displays a proMeal entity.
     *
     * @Route("p/home-meal/{hostName}/{mealTitle}/{mealID}",
     *     name="public_homemeal_hostname_mealtitle_id")
     * @Method("GET")
     */
    public function showHomeMealsByHostTitleIDAction(string $hostName, string $mealTitle, int $mealID)
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
        $meal = $this->get('api.home_meal.service')->restore($mealID)->getHomeMeal();
        if (null === $user || null === $meal) {
            throw new NotFoundHttpException('No Meal found!');
        }

        $this->get('api.seo')->enrichSEOWithHomeMeal($meal);

        return $this->render(
            '@WEBUI/HomeMeal/publicMealDetails.html.twig',
            array(
                'hostName' => $hostName,
                'mealTitle' => $mealTitle,
                'homeMeal' => $meal,
            )
        );
    }

    /**
     * Finds and displays a proMeal entity.
     *
     * @Route("p/home-meal/{hostName}/{mealTitle}",
     *     name="public_homemeal_hostname_mealtitle")
     * @Method("GET")
     */
    public function showHomeMealsByHostTitleAction(string $hostName, string $mealTitle)
    {
        if (null === $mealTitle) {
            throw new NotFoundHttpException('Mealtitle not set!');
        }
        if (null === $hostName) {
            throw new NotFoundHttpException('Hostname not set!');
        }
        // get user and meal
        $user = $this->get('api.user_manager')->findUserByUsername($hostName);
        $meals = $this->get('api.home_meal.service')->findByTitle($mealTitle);

        if (null === $user || 0 === \count($meals)) {
            throw new NotFoundHttpException('No Meals by title not found!');
        }

        $mealsSorted = CollectionHelper::sortByStartDate(new ArrayCollection($meals));

        // Enrich this call with SEO
        $this->get('api.seo')->enrichSEO();

        return $this->render(
            '@WEBUI/HomeMeal/meals.host.index.html.twig',
            array(
                'homeMeals' => $mealsSorted->toArray(),
                'hostName' => $hostName,
                'mealTitle' => $mealTitle,
            )
        );
    }

    /**
     * Home-Meal by ID, redirects to public_homemeal_hostname_mealtitle_id.
     *
     * @Route("p/home-meal/{id}", name="public_homemeal_show")
     * @Method("GET")
     */
    public function showHomeMealAction(HomeMeal $homeMeal)
    {
        return $this->redirectToRoute('public_homemeal_hostname_mealtitle_id', array(
                'mealTitle' => $homeMeal->getTitle(),
                'hostName' => $homeMeal->getHost()->getUsername(),
                'mealID' => $homeMeal->getId(),
            )
        );
    }

    /**
     * All Home-Meals.
     *
     * @Route("p/social-dining/home/", name="public_homemeal_index")
     * @Method("GET")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showHomeMealsIndexAction()
    {
        // Enrich this call with SEO
        $this->get('api.seo')->enrichSEO();

        // Get all HomeMeals ...
        $allHomeMeals = $this->get('api.home_meal.service')->findAll();

        // only starting today or in the future ...
        $allHomeMealsFiltered = CollectionHelper::filterBaseMealsEqualOrAfterStart(
            new ArrayCollection($allHomeMeals),
            new \DateTime('today'))->toArray();

        // Sort by start date ...
        $homeMealsSorted = CollectionHelper::sortByStartDate(new ArrayCollection($allHomeMealsFiltered));

        return $this->render(
            '@WEBUI/HomeMeal/meals.index.html.twig',
            array(
                'homeMeals' => $homeMealsSorted->toArray(),
            )
        );
    }

    /**
     * Home-Meals of a Host.
     *
     * @Route("p/social-dining/home/{hostName}",
     *     name="public_homemeal_hostname")
     * @Method("GET")
     */
    public function showHomeMealsOfHostAction(string $hostName = null)
    {
        $user = $this->get('api.user_manager')->findUserByUsername($hostName);

        if (null === $user) {
            throw new NotFoundHttpException('Unknown Host! Hostname: '.$hostName);
        }
        // Get all HomeMeals ...
        $allHostMeals = $this->get('api.home_meal.service')->findAllByOwner($user);
        // Sort by start date ...
        $mealsSorted = CollectionHelper::sortByStartDate(new ArrayCollection($allHostMeals));

        return $this->render(
            '@WEBUI/HomeMeal/meals.host.index.html.twig',
            array(
                'homeMeals' => $mealsSorted->toArray(),
                'hostName' => $hostName,
            )
        );
    }

    /**
     * Home-Meals of a Host.
     *
     * @Route("p/social-dining/home/heute",
     *     name="public_homemeal_hostname_today")
     * @Method("GET")
     */
    public function showHomeMealsOfTodayAction()
    {
        // Get all HomeMeals ...
        $allHostMeals = $this->get('api.home_meal.service')->findAllByStartDate(
           new DateTime('now')
        );
        // Sort by start date ...
        $mealsSorted = CollectionHelper::sortByStartDate(new ArrayCollection($allHostMeals));

        return $this->render(
            '@MMWebFront/HomeMeal/meals.host.index.html.twig',
            array(
                'homeMeals' => $mealsSorted->toArray(),
            )
        );
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param Request  $request
     * @param BaseMeal $baseMeal
     * @param string   $myArgument with a *description* of this argument, these may also
     *                             span multiple lines
     * @Route("p/home-meal/mm_{hash}", name="public_homemeal_byhash")
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function showHomeMealByHashAction(Request $request, string $hash)
    {
        $homeMeal = $this->findHomeMealByString($hash);

        if (null === $homeMeal) {
            throw new NotFoundHttpException('Meal not found!');
        }

        return $this->render(
            '@MMWebFront/HomeMeal/publicMealDetails.html.twig',
            array(
                'homeMeal' => $homeMeal,
            )
        );
    }

    /**
     * Finds a home meal by hash.
     *
     * @param string $hash
     *
     * @return HomeMeal|object|null
     */
    private function findHomeMealByString(string $hash)
    {
        // Try to find byHash ...
        /** @var HomeMeal $homeMeal */
        $homeMeal = $this->getDoctrine()->getRepository('ApiBundle:Meal\HomeMeal')->findOneBy(
            array(
                'hash' => $hash,
            )
        );
        // its ok to return null if nothing is found ...

        return $homeMeal;
    }
}
