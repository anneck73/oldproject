<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMWebFrontBundle\Controller;

use Mealmatch\ApiBundle\Controller\ApiController;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sonata\SeoBundle\Seo\SeoPage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * The WebFront Default Controller.
 *
 * - All Static pages
 * - Using variations sometimes ;)
 * - This controller is configured in services.yml available services are:
 *   * SeoPage: Sonata SEO Page Service.
 */
class DefaultController extends ApiController
{
    /**
     * The index page AND default route to *.mealmatch.de
     * It uses variations!
     * It uses Sonata SEO Page!
     *
     *
     *
     * @Route("/", name="home")
     * @Cache(expires="+1 minute");
     */
    public function indexAction(Request $request, SeoPage $seoPage)
    {
        try {
            $seoPage
                ->setTitle('Mealmatch deine Social-Dining Platform für Restaurants, Hotels und um Privat neue Leute kennen zu lernen.')
                ->addMeta('name', 'description', 'Mealmatch ist eine Online-Community-Plattform, die Menschen weltweit zu einem gemeinsamen Meal zusammenbringt. #mealmatch #social-dining')
                ->addMeta('property', 'og:title', 'Social-Dining, Leute kennenlernen | Mealmatch')
                ->addMeta('property', 'og:url',
                    $this->generateUrl('home', array(), UrlGeneratorInterface::ABSOLUTE_URL))
                ->addMeta('property', 'og:description', 'Beschreibung');

            /** @var array $allProMealsStartingToday */
            $allProMealsStartingToday = $this->get('api.search.service')->searchByCriteria(
                array(
                    'mealType' => 'ProMeal',
                    'city' => 'Köln',
                    'leaf' => '1',
                )
            );
        } catch (NotFoundExceptionInterface $notFoundException) {
            $this->logger->addWarning('Could not load SEO: '.$notFoundException->getMessage());
        } catch (ContainerExceptionInterface $containerException) {
            $this->logger->addWarning('Could not load SEO: '.$containerException->getMessage());
        }

        $viewData = array(
            'mealsInCityStartingToday' => $allProMealsStartingToday,
        );

        return $this->render(
            '@WEBUI/index.html.twig',
            $viewData
        );
    }
}
