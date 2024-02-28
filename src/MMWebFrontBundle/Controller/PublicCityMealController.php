<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMWebFrontBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Mealmatch\ApiBundle\Controller\ApiController;
use Mealmatch\ApiBundle\MealMatch\CollectionHelper;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * The Public City Controller shows Meals by City.
 * p/social-dining/{country}/{city}.
 */
class PublicCityMealController extends ApiController
{
    /**
     * Redirect to enable city/$city-name.
     *
     * @Route("city/{city}", name="public_meals_city_named")
     * @Method("GET")

     *
     * @param Request $request
     * @param string  $city
     *
     * @return RedirectResponse
     */
    public function showMealsByCityRedirectAction(Request $request, string $city): RedirectResponse
    {
        $this->redirectToRoute('public_meals_city',
            array(
                'country' => 'DE',
                'city' => $city,
            ));
    }

    /**
     * Shows all meals running in the city specified.
     *
     * @Route("p/social-dining/{country}/{city}", name="public_meals_city",
     *     requirements={"country": "DE|UK|FR"},
     *     defaults={"country": "DE"}
     *  )
     * @Method("GET")
     */
    public function showMealsByCityAction(Request $request, string $city, string $country)
    {
        $keywords = "social-dining in $city";

        try {
            $seoPage = $this->container->get('sonata.seo.page');

            $seoPage
                ->setTitle("Mealmatch | Social-Dining in $city")
                ->addMeta('name', 'keywords', $keywords)
                ->addMeta('name', 'description', "Das Mealmatch Meal-Angebot für social-dining in der Stadt $city")
                ->addMeta('property', 'og:title', "Mealmatch | Meal-Angebote in $city")
                ->addMeta('property', 'product:age_group', 'adult')
                ->addMeta(
                    'property',
                    'og:url',
                    $this->generateUrl(
                        'public_meals_city',
                        array('city' => $city),
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                )
                ->addMeta('property', 'og:description', "Das Mealmatch Meal-Angebot für social-dining in der Stadt $city")
            ;
        } catch (NotFoundExceptionInterface $notFoundException) {
            $this->logger->addWarning('Could not load SEO: '.$notFoundException->getMessage());
        } catch (ContainerExceptionInterface $containerException) {
            $this->logger->addWarning('Could not load SEO: '.$containerException->getMessage());
        }

        $cityMeals = $this->get('api.search.service')->searchByCriteria(
            array(
                'city' => $city,
            )
        );
        // only starting today or in the future ...
        $cityMeals = CollectionHelper::filterBaseMealsEqualOrAfterStart(
            new ArrayCollection($cityMeals),
            new \DateTime('today'))->toArray();

        $viewData = array(
            'cityMeals' => $cityMeals,
            'city' => $city,
            'country' => 'de',
            'viewData' => array(
                'title' => $this->trans('public.city.meals.title'),
            ),
        );

        return $this->render(
            '@WEBUI/City/meals.index.html.twig',
            $viewData
        );
    }
}
