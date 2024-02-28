<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\UIPublicBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealCategory;
use Mealmatch\ApiBundle\Entity\Meal\HomeMeal;
use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * The SitemapController Class is responsible for generating a sitemap to leverage Google-Page rankings.
 * The sitemap should include links to all public meals, restaurants and other SEO relevant content.
 */
class SitemapController extends Controller
{
    // YYYY-MM-DDThh:mmTZD
    const SITEMAP_DATETIME_FORMAT = 'Y-m-dTh:iTZD';

    /**
     * @Route("/sitemap.{_format}", name="sitemap", Requirements={"_format" = "xml"})
     * @Template("@WEBUI/Sitemap/sitemap.xml.twig")
     */
    public function sitemapAction(Request $request)
    {
        $urls = array();
        $hostname = $request->getHost();
        $router = $this->get('router');
        // homepage
        $urls[] = array(
            'loc' => $router->generate('home', array(), UrlGeneratorInterface::ABSOLUTE_URL),
            'changefreq' => 'weekly',
            'priority' => '1.0',
        );

        $languages = array('de', 'en');

        // multi-lang pages
        foreach ($languages as $lang) {
            $urls[] = array(
                'loc' => $router->generate(
                    'mm_how_it_works',
                    array('_locale' => $lang),
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'changefreq' => 'monthly',
                'priority' => '0.3',
            );
            $urls[] = array(
                'loc' => $router->generate(
                    'mm_about',
                    array('_locale' => $lang),
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'changefreq' => 'monthly',
                'priority' => '0.3',
            );
            $urls[] = array(
                'loc' => $router->generate(
                    'mm_career',
                    array('_locale' => $lang),
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'changefreq' => 'monthly',
                'priority' => '0.3',
            );
            $urls[] = array(
                'loc' => $router->generate(
                    'mm_events',
                    array('_locale' => $lang),
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'changefreq' => 'monthly',
                'priority' => '0.3',
            );
            $urls[] = array(
                'loc' => $router->generate(
                    'mm_how_to_host',
                    array('_locale' => $lang),
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'changefreq' => 'monthly',
                'priority' => '0.3',
            );
            $urls[] = array(
                'loc' => $router->generate(
                    'mm_press',
                    array('_locale' => $lang),
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'changefreq' => 'monthly',
                'priority' => '0.3',
            );
            $urls[] = array(
                'loc' => $router->generate(
                    'mm_how_it_works',
                    array('_locale' => $lang),
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'changefreq' => 'monthly',
                'priority' => '0.3',
            );
        }

        // Cities Köln, Essen
        $urls[] = array(
            'loc' => $router->generate(
                'public_meals_city',
                array('_locale' => 'de', 'city' => 'Köln'),
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'changefreq' => 'weekly',
            'priority' => '0.7',
        );
        $urls[] = array(
            'loc' => $router->generate(
                'public_meals_city',
                array('_locale' => 'de', 'city' => 'Essen'),
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'changefreq' => 'weekly',
            'priority' => '0.7',
        );
        // Country
        $urls[] = array(
            'loc' => $router->generate(
                'public_meals_country_index',
                array('_locale' => 'de', 'country' => 'DE'),
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'changefreq' => 'weekly',
            'priority' => '0.7',
        );

        $urls = $this->createPublicProMealsSeo($urls);
        $urls = $this->createPublicHomeMealsSeo($urls);
        $urls = $this->createPublicCategoryMealsSeo($urls);

        return array('urls' => $urls, 'hostname' => $hostname);
    }

    private function createPublicCategoryMealsSeo(array $urls): array
    {
        /** @var ArrayCollection $catCol */
        $catCol = $this->get('api.meal.service')->getAllCategories();

        /** @var BaseMealCategory $cat */
        foreach ($catCol as $cat) {
            $urls = $this->fillWithRoute('public_meals_category_essen',
                array('cat' => strtolower($cat->getName())),
                array('lastmod' => $cat->getUpdatedAt()->format(DATE_W3C)),
                $urls);
        }

        return $urls;
    }

    private function createPublicProMealsSeo(array $urls): array
    {
        /** @var ProMeal $proMeal */
        foreach ($this->get('api.pro_meal.service')->findAll() as $proMeal) {
            $urls = $this->fillWithRoute('public_restaurant_tabletopic',
                array('tableTopic' => $proMeal->getTableTopic()),
                array('lastmod' => $proMeal->getUpdatedAt()->format(DATE_W3C)),
                $urls);
            $urls = $this->fillWithRoute('public_promeal_hostname',
                array('hostName' => $proMeal->getHost()->getUsername()),
                array('lastmod' => $proMeal->getUpdatedAt()->format(DATE_W3C)),
                $urls);
            $urls = $this->fillWithRoute('public_promeal_hostname_mealtitle',
                array('hostName' => $proMeal->getHost()->getUsername(),
                      'mealTitle' => $proMeal->getTitle(), ),
                array('lastmod' => $proMeal->getUpdatedAt()->format(DATE_W3C)),
                $urls);
            $urls = $this->fillWithRoute('public_promeal_hostname_mealtitle_id',
                array('hostName' => $proMeal->getHost()->getUsername(),
                      'mealTitle' => $proMeal->getTitle(),
                      'mealID' => $proMeal->getId(), ),
                array('lastmod' => $proMeal->getUpdatedAt()->format(DATE_W3C)),
                $urls);
        }

        return $urls;
    }

    private function createPublicHomeMealsSeo(array $urls): array
    {
        /** @var HomeMeal $homeMeal */
        foreach ($this->get('api.home_meal.service')->findAll() as $homeMeal) {
            $urls = $this->fillWithRoute('public_homemeal_hostname',
                array('hostName' => $homeMeal->getHost()->getUsername()),
                array('lastmod' => $homeMeal->getUpdatedAt()->format(DATE_W3C)),
                $urls);
            $urls = $this->fillWithRoute('public_homemeal_hostname_mealtitle',
                array('hostName' => $homeMeal->getHost()->getUsername(),
                      'mealTitle' => $homeMeal->getTitle(), ),
                array('lastmod' => $homeMeal->getUpdatedAt()->format(DATE_W3C)),
                $urls);
            $urls = $this->fillWithRoute('public_homemeal_hostname_mealtitle_id',
                array('hostName' => $homeMeal->getHost()->getUsername(),
                      'mealTitle' => $homeMeal->getTitle(),
                    'mealID' => $homeMeal->getId(), ),
                array('lastmod' => $homeMeal->getUpdatedAt()->format(DATE_W3C)),
                $urls);
        }

        return $urls;
    }

    /**
     * Helper to fill the URL's array with the given routeName.
     *
     * @param string $routeName
     * @param array  $routeParameters
     * @param array  $options
     * @param array  $urls
     *
     * @return array
     */
    private function fillWithRoute(string $routeName, array $routeParameters, array $options, array $urls): array
    {
        // @todo: make this overwriteable!
        // Merging in defaults...
        $options = array_merge(
            array(
                'priority' => '0.5',
                'changefreq' => 'weekly',
            ), $options
        );
        $urls[] = array(
            'loc' => $this->get('router')->generate(
                $routeName,
                $routeParameters,
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'priority' => $options['priority'],
            'changefreq' => $options['changefreq'],
            'lastmod' => $options['lastmod'],
        );

        return $urls;
    }
}
