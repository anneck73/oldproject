<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\SearchBundle\Controller;

use Mealmatch\ApiBundle\Controller\ApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * The SearchController uses the 'api.search.service' to query for all kind of meals.
 *
 *
 * @uses \SearchService
 *
 * @Route("/search")
 */
class SearchController extends ApiController
{
    /**
     * Shows the HomeMeal Gmaps search.
     *
     * @param Request $request
     * @Route("/home", name="search_home")
     * @Method("GET")
     */
    public function homeSearchAction(Request $request)
    {
        // Setting SEO Title n stuff ...
        $getParams = $this->get('request_stack')->getCurrentRequest()->query->all();

        if (\in_array('city', $getParams, true)) {
            $city = $getParams['city'];
            $this->get('api.seo')->setTitle('Mealmatch Social-Dining | Suchergebnisse für Home-Meals in '.$city);
            $this->get('api.seo')->addMeta('name', 'description',
                'Suchergebnisse für social-dining Home-Meals von privaten Gastgebern in '.$city);
            $this->get('api.seo')->addMeta('name', 'keywords',
                'Mealmatch social-dining home-meals '.$city);
        } else {
            $this->get('api.seo')->setTitle('Mealmatch Social-Dining | Suchergebnisse für Home-Meals');
            $this->get('api.seo')->addMeta('name', 'description',
                'Suchergebnisse für social-dining Home-Meals von privaten Gastgebern.');
            $this->get('api.seo')->addMeta('name', 'keywords',
                '');
        }

        $viewData = $this->get('api.search.service')->search($request, 'HomeMeal');

        return $this->render(
            '@WEBUI/Search/search_do_home.html.twig',
            $viewData
        );
    }

    /**
     * Returns searchresults for HomeMeals as JSON.
     *
     * @param Request $request
     * @Route("/home/json", name="search_home_json")
     * @Method("GET")
     */
    public function homeSearchJsonAction(Request $request)
    {
        $searchResult = $this->get('api.search.service')->search($request, 'HomeMeal');

        return new JsonResponse($searchResult['mealsJSON']);
    }

    /**
     * Shows the ProMeal Gmaps search.
     *
     * @param Request $request
     * @Route("/meal", name="search_pro")
     * @Method("GET")
     */
    public function proSearchAction(Request $request)
    {
        $viewData = $this->get('api.search.service')->search($request, 'ProMeal');

        return $this->render(
            '@WEBUI/Search/search_do_pro.html.twig',
            $viewData
        );
    }

    /**
     * Returns searchresults for ProMeals as JSON.
     *
     * @param Request $request
     * @Route("/meal/json", name="search_pro_json")
     * @Method("GET")
     */
    public function proSearchJsonAction(Request $request)
    {
        $searchResult = $this->get('api.search.service')->search($request, 'ProMeal');

        return new JsonResponse($searchResult['mealsJSON']);
    }

    /**
     * The search "entry" from start page "chooser".
     *
     * @param Request $request
     * @Route("/do", name="search_do")
     * @Method("GET")
     */
    public function doSearchAction(Request $request)
    {
        // SEO Search Results ...
        $this->get('api.seo')->enrichSearchResultsFromReq($request);

        if ('home' === $request->get('mealType')) {
            $viewData = $this->get('api.search.service')->search($request, 'HomeMeal');

            return $this->render('@WEBUI/Search/search_do_home.html.twig', $viewData);
        }
        if ('pro' === $request->get('mealType')) {
            $viewData = $this->get('api.search.service')->search($request, 'ProMeal');

            return $this->render('@WEBUI/Search/search_do_pro.html.twig', $viewData);
        }
        // If you are here, someone just typed in the url, and will see a search of BaseMeals ;)
        // or from the startpage without choosing Home/ProMeal (none selected)
        $viewData = $this->get('api.search.service')->search($request);

        return $this->render('@WEBUI/Search/search_do.html.twig', $viewData);
    }

    /**
     * Returns the search results as JSON.
     *
     * @param Request $request
     * @Route("/do/json/", name="search_do_json")
     * @Method("GET")
     */
    public function doSearchJsonAction(Request $request)
    {
        $searchResult = $this->get('api.search.service')->search($request);

        return new JsonResponse($searchResult['mealsJSON']);
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param Request $request
     * @Route("/json/{distance}/{point}", name="search_distance_json")
     * @Method("GET")
     */
    public function doSearchDistanceJsonAction(Request $request, string $distance, string $point)
    {
        $searchResult = $this->get('api.search.service')->search($request);

        return new JsonResponse($searchResult['mealsJSON']);
    }
}
