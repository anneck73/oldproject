<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMWebFrontBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Controller\ApiController;
use Mealmatch\ApiBundle\MealMatch\CollectionHelper;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * The Public City Controller shows Meals by City.
 *
 * @Route("p/social-dining")
 */
class PublicMealsController extends ApiController
{
    /**
     * Shows all meals running in the country specified.
     * !Only DE,UK,FR are currently used!
     *
     *
     *
     * @Route("/", name="public_meals_index")
     *
     * @Method("GET")
     */
    public function showAllMealsAction()
    {
        $keywords = 'Deine Social Dining Angebote. Meals bei Freunden zuhause und mit neuen Freunden im Restaurant! Gemeinsam Essen';

        try {
            $seoPage = $this->container->get('sonata.seo.page');

            $seoPage
                ->setTitle('Mealmatch | Social Dining - Alle Angebote')
                ->addMeta('name', 'keywords', $keywords)
                ->addMeta('name', 'description',
                    'Deine Social Dining Angebote - Alle Angebote')
                ->addMeta('property', 'og:title', 'Mealmatch | Meal-Angebote')
                ->addMeta('property', 'product:age_group', 'adult')
                ->addMeta(
                    'property',
                    'og:url',
                    $this->generateUrl(
                        'public_meals_index',
                        array(),
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                )
                ->addMeta('property', 'og:description', 'Mealmatch alle Meal-Angebot für Social Dining.')
            ;
        } catch (NotFoundExceptionInterface $notFoundException) {
            $this->logger->addWarning('Could not load SEO: '.$notFoundException->getMessage());
        } catch (ContainerExceptionInterface $containerException) {
            $this->logger->addWarning('Could not load SEO: '.$containerException->getMessage());
        }

        $allMeals = $this->get('api.meal.service')->findAllBy(array('status' => ApiConstants::MEAL_STATUS_RUNNING));
        // only starting today or in the future ...
        $allMealsFiltered = CollectionHelper::filterBaseMealsEqualOrAfterStart(
            new ArrayCollection($allMeals),
            new \DateTime('today'))->toArray();

        $viewData = array(
            'allMeals' => $allMealsFiltered,
            'viewData' => array(
                'title' => $this->trans('public.meals.title'),
            ),
        );

        return $this->render(
            '@WEBUI/Meals/meals.index.html.twig',
            $viewData
        );
    }

    /**
     * Shows all meals running in the country specified.
     * !Only DE,UK,FR are currently used!
     *
     *
     *
     * @Route("/{country}", name="public_meals_country_index",
     *     requirements={"country": "DE|UK|FR"})
     *
     * @Method("GET")
     */
    public function showMealsByCountryAction(string $country)
    {
        $countries = array(
            'DE' => 'Deutschland',
            'UK' => 'Großbritannien',
            'FR' => 'Frankreich',
        );
        $keywords = 'Deine Social Dining Angebote in '.$countries[$country]
            .' ('.$country.'). Meals bei Freunden zuhause und mit neuen Freunden im Restaurant! Gemeinsam Essen';

        try {
            $seoPage = $this->container->get('sonata.seo.page');

            $seoPage
                ->setTitle('Mealmatch | Social Dining in '.$countries[$country]
                    .' ('.$country.')')
                ->addMeta('name', 'keywords', $keywords)
                ->addMeta('name', 'description',
                    'Deine Social Dining Angebote in '.$countries[$country]
                    .' ('.$country.') Meals bei guten Freunden zuhause und mit neuen Freunden im Restaurant! Gemeinsam Essen'
                    .' und beim Tischgespräch, mit einem Tischthema, zusammen finden.')
                ->addMeta('property', 'og:title', 'Mealmatch | Meal-Angebote')
                ->addMeta('property', 'product:age_group', 'adult')
                ->addMeta(
                    'property',
                    'og:url',
                    $this->generateUrl(
                        'public_meals_country_index',
                        array('country' => $country),
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                )
                ->addMeta('property', 'og:description', 'Mealmatch Meal-Angebot für Social Dining in '.$country)
            ;
        } catch (NotFoundExceptionInterface $notFoundException) {
            $this->logger->addWarning('Could not load SEO: '.$notFoundException->getMessage());
        } catch (ContainerExceptionInterface $containerException) {
            $this->logger->addWarning('Could not load SEO: '.$containerException->getMessage());
        }

        $allMeals = $this->get('api.search.service')->searchByCriteria(array('country' => $country));
        // only starting today or in the future ...
        $allMealsFiltered = CollectionHelper::filterBaseMealsEqualOrAfterStart(
            new ArrayCollection($allMeals),
            new \DateTime('today'))->toArray();

        $viewData = array(
            'allMeals' => $allMealsFiltered,
            'country' => $country,
            'viewData' => array(
                'title' => $this->trans('public.city.meals.title'),
            ),
        );

        return $this->render(
            '@WEBUI/Meals/meals.country.index.html.twig',
            $viewData
        );
    }
}
