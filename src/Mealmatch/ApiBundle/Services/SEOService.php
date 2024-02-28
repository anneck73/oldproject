<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Services;

use Doctrine\ORM\EntityManager;
use Mealmatch\ApiBundle\Entity\Meal\HomeMeal;
use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Monolog\Logger;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Sonata\SeoBundle\Seo\SeoPage;
use Sonata\SeoBundle\Seo\SeoPageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class SEOService does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class SEOService
{
    /** @var Logger $logger */
    protected $logger;
    /**
     * @todo: Finish PHPDoc!
     *
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @todo: Finish PHPDoc!
     *
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @todo: Finish PHPDoc!
     *
     * @var SeoPage
     */
    private $seoPage;
    /**
     * @todo: Finish PHPDoc!
     *
     * @var Router
     */
    private $router;

    /**
     * SEOService constructor.
     */
    public function __construct(
        EntityManager $entityManager,
        Logger $logger,
        RequestStack $requestStack,
        SeoPage $seoPage,
        Router $router)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->seoPage = $seoPage;
        $this->router = $router;
    }

    public function enrichSEO(array $metaData = null)
    {
        $this->seoPage
            ->setTitle('Mealmatch Meat & Eat | Social Dining - Gemeinsam essen.')
            ->addMeta('name', 'keywords', 'social-dining lecker essen freunde finden dinner gemeinsam speisen')
            ->addMeta('name', 'description', 'Mealmatch ist ... ');

        if (null !== $metaData) {
            foreach ($metaData as $m) {
                $this->addMeta($m['type'], $m['name'], $m['value']);
            }
        }
    }

    public function enrichSEOWithProMeal(ProMeal $proMeal)
    {
        $keywords =
            $proMeal->getCountryCategory().' '.
            $proMeal->getMealType().' '.
            $proMeal->getTableTopic().' '.
            $proMeal->getCity().' '.
            $proMeal->getAddress()->getSublocality().' '.
            $proMeal->getAddress()->getPostalCode().' '.
            $proMeal->getStartDateTime()->format('d.m.Y h:i').' ';

        $keywords .= 'social-dining dinner supperclub gemeinsam speisen ';
        $keywords .= $proMeal->getHost()->getRestaurantProfile()->getType().' ';
        $keywords .= $proMeal->getHost()->getUsername().' ';

        try {
            $this->seoPage
                ->setTitle($proMeal->getHost()->getUsername().
                    ' | '.$proMeal->getTitle().
                    ' am '.$proMeal->getStartDateTime()->format('d.m.Y H:i'))
                ->addMeta('name', 'keywords', $keywords)
                ->addMeta('name', 'description', $proMeal->getDescription())
                ->addMeta('property', 'og:title', $proMeal->getHost()->getUsername().' '.$proMeal->getTitle())
                ->addMeta('property', 'product:price:amount', $proMeal->getMinOfferPrice())
                ->addMeta(
                    'property',
                    'product:price:currency',
                    $proMeal->getHost()->getRestaurantProfile()->getDefaultCurrency()
                )
                ->addMeta('property', 'product:age_group', 'adult')
                ->addMeta(
                    'property',
                    'product:expiration_time',
                    $proMeal->getMealEvent()->getStartDateTime()->format('d.m.Y H:i')
                )
                ->addMeta(
                    'property',
                    'og:url',
                    $this->router->generate(
                        'public_homemeal_show',
                        array('id' => $proMeal->getId()),
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                )
                ->addMeta('property', 'og:description', $proMeal->getDescription())
                ->addMeta('name', 'twitter:card', $proMeal->getTableTopic())
                ->addMeta('name', 'twitter:site', '@mealmatchAPP')
                ->addMeta('name', 'twitter:creator', '@mealmatchAPP')
                ->addMeta('name', 'twitter:title', $proMeal->getTableTopic())
                ->addMeta('name', 'twitter:description', $proMeal->getDescription())

            ;
        } catch (NotFoundExceptionInterface $notFoundException) {
            $this->logger->addWarning('Could not load SEO: '.$notFoundException->getMessage());
        } catch (ContainerExceptionInterface $containerException) {
            $this->logger->addWarning('Could not load SEO: '.$containerException->getMessage());
        }
    }

    public function enrichSEOWithHomeMeal(HomeMeal $homeMeal)
    {
        $keywords = $homeMeal->getCity().' '.
            $homeMeal->getCountryCategory().' '.
            $homeMeal->getMealType().' '.
            $homeMeal->getMealMain().' '.
            $homeMeal->getMealDesert().' '.
            $homeMeal->getMealStarter();

        try {
            $this->seoPage
                ->setTitle($homeMeal->getHost()->getUsername().
                    ' | '.$homeMeal->getTitle().
                    ' am '.$homeMeal->getStartDateTime()->format('d.m.Y H:i'))
                ->addMeta('name', 'keywords', $keywords)
                ->addMeta('name', 'description', $homeMeal->getDescription())
                ->addMeta('property', 'og:title', $homeMeal->getHost()->getUsername().' '.$homeMeal->getTitle())
                ->addMeta('property', 'product:price:amount', $homeMeal->getSharedCost())
                ->addMeta('property', 'product:price:currency', $homeMeal->getSharedCostCurrency())
                ->addMeta('property', 'product:category', $homeMeal->getCategories()->first()->getName())
                // ->addMeta('property', 'product:age_group', 'adult')
                // ->addMeta('property', 'product:expiration_time', $homeMeal->getMealEvent()->getEndDateTime())

                ->addMeta('property', 'og:url',
                    $this->router->generate('public_homemeal_show', array('id' => $homeMeal->getId()),
                        UrlGeneratorInterface::ABSOLUTE_URL))
                ->addMeta('property', 'og:description', $homeMeal->getDescription());
        } catch (NotFoundExceptionInterface $notFoundException) {
            $this->logger->addWarning('Could not load SEO: '.$notFoundException->getMessage());
        } catch (ContainerExceptionInterface $containerException) {
            $this->logger->addWarning('Could not load SEO: '.$containerException->getMessage());
        }
    }

    public function enrichSearchResultsFromReq(Request $request): SeoPageInterface
    {
        $title = 'Mealmatch Social-Dining | Deine Suche ';

        // Glue the title together, ORDER IS IMPORTANT

        if ('home' === $request->get('mealType')) {
            $title .= 'nach Home-Meals ';
        }
        if ('pro' === $request->get('mealType')) {
            $title .= 'nach Restaurant-Meals ';
        }

        if (null !== $request->get('searchLocation')
        && '' !== $request->get('searchLocation')) {
            $title .= ' in '.$request->get('searchLocation').' ';
        } else {
            $title .= ' social dining angeboten.';
        }

        return $this->setTitle($title);
    }

    /**
     * SEO Helper to add a meta-tag.
     *
     * @param string $type
     * @param string $name
     * @param string $value
     *
     * @return SeoPageInterface
     */
    public function addMeta(string $type, string $name, string $value): SeoPageInterface
    {
        return $this->seoPage->addMeta($type, $name, $value);
    }

    /**
     * SEO Helper to set Title Tag.
     *
     * @param string $titleTag
     *
     * @return SeoPageInterface
     */
    public function setTitle(string $titleTag): SeoPageInterface
    {
        return $this->seoPage->setTitle($titleTag);
    }
}
