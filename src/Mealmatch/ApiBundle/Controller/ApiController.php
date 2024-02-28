<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;
use Mealmatch\ApiBundle\Entity\Meal\HomeMeal;
use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Mealmatch\ApiBundle\MealMatch\CollectionHelper;
use MMUserBundle\Entity\MMRestaurantProfile;
use MMUserBundle\Entity\MMUser;
use Monolog\Logger;
use ReflectionObject;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Component\Translation\IdentityTranslator;

/**
 * @todo: Finish PHPDoc!
 * Baseclass for all ApiControllers to extend!
 * Call init() before using methods!
 *
 * Initializes: Logger, EntityManager, Translator
 *
 * Provides "variation" functionality for template path resolution.
 */
abstract class ApiController extends Controller
{
    const REQ_PARAM_VARIANT = 'variant';

    /**
     * Logger for controllers ...
     *
     * @var Logger
     */
    protected $logger;

    /**
     * The EntityManager ...
     *
     * @var EntityManager ;
     */
    protected $em;

    /**
     * The symfony translator ...
     *
     * @var object|DataCollectorTranslator|IdentityTranslator
     */
    protected $translator;

    /**
     * Common initialization for ApiControllers.
     */
    protected function init(): void
    {
        $this->logger = $this->get('monolog.logger.mealmatch');
        $this->em = $this->get('doctrine.orm.default_entity_manager');
        $this->translator = $this->get('translator');
    }

    /**
     * @param array $searchOptions
     *
     * @return array
     */
    protected function createAllProMealViewData(array $searchOptions): array
    {
        /** @var MMUser $owner */
        $owner = $this->getUser();

        /** @var MMRestaurantProfile $restaurantProfile */
        $restaurantProfile = $owner->getRestaurantProfile();

        /** @var ArrayCollection $allMeals */
        $allMeals = CollectionHelper::sortByStartDate(
            new ArrayCollection($this->get('api.pro_meal.service')->findAllByOwner($owner))
        );

        // View Specific Data
        // @todo: translation missing!!!
        $viewData = array(
            'title' => 'Meine Meals',
            'subtitle' => 'Hier siehst Du alle deine Restaurant-Meals und kannst diese bearbeiten.',
        );

        return array(
            'allMeals' => $allMeals,
            'rProfile' => $restaurantProfile,
            'viewData' => $viewData,
        );
    }

    /**
     * Create all the required data for the template to render!
     * This helper generates everything for the HomeMeal:index or list view of HomeMeals.
     *
     * @return array render view-data
     */
    protected function createAllHomeMealViewData(): array
    {
        $owner = $this->getUser();
        $runningHomeMeals = $this->get('api.home_meal.service')->findRunningByOwner($owner);
        $stoppedHomeMeals = $this->get('api.home_meal.service')->findStoppedByOwner($owner);
        $createdHomeMeals = $this->get('api.home_meal.service')->findCreatedByOwner($owner);
        $readyHomeMeals = $this->get('api.home_meal.service')->findReadyByOwner($owner);
        $finishedHomeMeals = $this->get('api.home_meal.service')->findFinishedByOwner($owner);

        $availableDates = new ArrayCollection();
        $childMeals = new ArrayCollection();

        // Running HomeMeals
        /** @var HomeMeal $homeMeal */
        foreach ($runningHomeMeals as $homeMeal) {
            $availDates = $this->get('api.meal_event.service')->getAvailableDatesForHomeMeal($homeMeal)->toArray();
            $availableDates->set($homeMeal->getId(), $availDates);
            if ($homeMeal->isRootNode()) {
                // @todo: hmmm ... $proMealTree = $this->get('api.pro_meal.service')->getTree($homeMeal);
                $childMeals->set($homeMeal->getId(), $this->get('api.home_meal.service')->getTree($homeMeal));
            }
        }

        // Created by current user (owner)
        /* @var HomeMeal $proMeal */
        foreach ($createdHomeMeals as $homeMeal) {
            $availDates = $this->get('api.meal_event.service')->getAvailableDatesForHomeMeal($homeMeal)->toArray();
            $availableDates->set($homeMeal->getId(), $availDates);
            if ($homeMeal->isRootNode()) {
                // @todo: hmmm ... $proMealTree = $this->get('api.pro_meal.service')->getTree($homeMeal);
                $childMeals->set($homeMeal->getId(), $this->get('api.home_meal.service')->getTree($homeMeal));
            }
        }
        // Ready by current user (owner)
        /* @var HomeMeal $proMeal */
        foreach ($readyHomeMeals as $homeMeal) {
            $availDates = $this->get('api.meal_event.service')->getAvailableDatesForHomeMeal($homeMeal)->toArray();
            $availableDates->set($homeMeal->getId(), $availDates);
            if ($homeMeal->isRootNode()) {
                // @todo: hmmm ... $proMealTree = $this->get('api.pro_meal.service')->getTree($homeMeal);
                $childMeals->set($homeMeal->getId(), $this->get('api.home_meal.service')->getTree($homeMeal));
            }
        }
        // STOPPED by current user (owner)
        /* @var HomeMeal $proMeal */
        foreach ($stoppedHomeMeals as $homeMeal) {
            $availDates = $this->get('api.meal_event.service')->getAvailableDatesForHomeMeal($homeMeal)->toArray();
            $availableDates->set($homeMeal->getId(), $availDates);
            if ($homeMeal->isRootNode()) {
                // @todo: hmmm ... $proMealTree = $this->get('api.pro_meal.service')->getTree($homeMeal);
                $childMeals->set($homeMeal->getId(), $this->get('api.home_meal.service')->getTree($homeMeal));
            }
        }
        // FINISHED by current user (owner)
        /* @var HomeMeal $proMeal */
        foreach ($finishedHomeMeals as $homeMeal) {
            $availDates = $this->get('api.meal_event.service')->getAvailableDatesForHomeMeal($homeMeal)->toArray();
            $availableDates->set($homeMeal->getId(), $availDates);
            if ($homeMeal->isRootNode()) {
                // @todo: hmmm ... $proMealTree = $this->get('api.pro_meal.service')->getTree($homeMeal);
                $childMeals->set($homeMeal->getId(), $this->get('api.home_meal.service')->getTree($homeMeal));
            }
        }

        $allMeals = array_merge(
            $runningHomeMeals,
            $createdHomeMeals,
            $readyHomeMeals,
            $stoppedHomeMeals
        // $finishedHomeMeals
        );
        $allChildMeals = $this->getOnlyChildNodes($allMeals);

        // View Specific Data
        $viewData = array(
            'title' => 'Meine Meals',
            'subtitle' => 'Hier siehst Du alle deine Home-Meals und kannst diese bearbeiten.',
        );

        return array(
            'allMeals' => $allMeals,
            'allChildMeals' => $allChildMeals,
            'homeMeals' => $runningHomeMeals,
            'cHomeMeals' => $createdHomeMeals,
            'rHomeMeals' => $readyHomeMeals,
            'stoppedHomeMeals' => $stoppedHomeMeals,
            'aDates' => $availableDates->toArray(),
            'childMeals' => $childMeals->toArray(),
            'viewData' => $viewData,
        );
    }

    /**
     * @param Request $request
     *
     * @return int
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     */
    protected function getSelectedTab(Request $request): int
    {
        return $request->get('selectedTab') ?? '1';
    }

    /**
     * @param $allMeals
     *
     * @return array
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     */
    protected function getOnlyChildNodes($allMeals): array
    {
        // Now filter all meals who are root-Meals.
        // But only HomeMeal and ProMeal have Childs...
        // don't check on instanceof BaseMeal ... ALL Meals are BaseMeals.
        $allChildMeals = array_filter(
            $allMeals,
            function ($meal) {
                if ($meal instanceof HomeMeal) {
                    if ($meal->isRootNode()) {
                        return false;
                    }
                }
                if ($meal instanceof ProMeal) {
                    if ($meal->isRootNode()) {
                        return false;
                    }
                }

                return true;
            }
        );

        return $allChildMeals;
    }

    protected function getPercentageFilled($entityData, $entityClass, $addToAllFieldCount = 0): float
    {
        if (null === $entityData) {
            return 0;
        }
        $entityManager = $this->getDoctrine()->getManager();

        $properties = $entityManager->getClassMetadata($entityClass)->getFieldNames();
        $allFieldsCount = \count($properties) + $addToAllFieldCount;
        $output = array_merge(
            $properties,
            $entityManager->getClassMetadata($entityClass)->getAssociationNames()
        );

        $reflector = new ReflectionObject($entityData);
        $count = 0;

        foreach ($output as $property) {
            $method = $reflector->getMethod('get'.ucfirst($property));
            $method->setAccessible(true);
            $result = $method->invoke($entityData);
            if ($result instanceof PersistentCollection) {
                $collectionReflector = new \ReflectionObject($result);
                $method = $collectionReflector->getMethod('count');
                $method->setAccessible(true);
                $result = $method->invoke($result);
                $count += $result;
            } else {
                null === $result ?: $count++;
            }
        }

        return $count / $allFieldsCount * 100;
    }

    /**
     * Returns the translated value of the key from the 'Mealmatch' domain.
     *
     * @param string $key the key to translate
     *
     * @return string the translated value
     */
    protected function trans(string $key): string
    {
        $this->init();

        return $this->translator->trans($key, array(), 'Mealmatch');
    }

    /**
     * Helper Method to determine the variation to use and "stick" it to the session.
     * Can be overwritten with new value.
     *
     * @param Request $request the current request
     *
     * @return mixed|string the variation to use
     */
    protected function determineVariation(Request $request)
    {
        $this->init();
        $appName = getenv('APP_NAME');
        $symfonyEnv = $this->getParameter('kernel.environment');
        switch ($symfonyEnv) {
            case 'dev':
                if ('mealmatch-local' === $appName) {
                    $symfonyBasedVariant = 'local';
                } else {
                    $symfonyBasedVariant = 'dev';
                }
                break;
            case 'pipeline':
            case 'prod':
            case 'test':
                $symfonyBasedVariant = 'prod';
                break;
            default:
                $symfonyBasedVariant = 'dev';
                break;
        }

        // Default variant
        $defaultVariant = $symfonyBasedVariant;
        $this->logger->addWarning('SYMF_ENV: '.$symfonyEnv.' -> Default Variant is: '.$defaultVariant);
        // Is there a variant in the session ?
        if ($this->get('session')->has(self::REQ_PARAM_VARIANT)) {
            // YES ? ok use it !, just to be sure with a default.
            $variant = $this->get('session')->get(self::REQ_PARAM_VARIANT, $defaultVariant);
            // What if we want to override by giving a different variant with the request ...
            if (null !== $request->get(self::REQ_PARAM_VARIANT)) {
                // Parameter in get request overwrites the last one in the session ...
                $variant = $request->get(self::REQ_PARAM_VARIANT);
            }
        } else {
            // no variant in session ... but maybe in request ...
            if (null !== $request->get(self::REQ_PARAM_VARIANT)) {
                // found in request, use it!
                $variant = $request->get(self::REQ_PARAM_VARIANT);
            } else {
                // nothing is found about a variant, so we choose the default one ...
                $variant = $defaultVariant;
            }
        }

        // stick it to the session ...
        $this->get('session')->set(self::REQ_PARAM_VARIANT, $variant);
        $this->logger->addWarning('Set Variant into session: '.$variant);
        $this->get('session')->save();

        // and return the resulting variant/variation :)
        return $variant;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $pTemplateName the default template name, if no variation is found this template is used
     *
     * @return string the template found
     */
    protected function getVariationTemplate(string $pTemplateName = ''): string
    {
        $this->init();
        $defaultTemplateName = '@WEBUI/StaticPages/'.$pTemplateName;

        if ($this->get('session')->has('variant')) {
            $variant = $this->get('session')->get('variant');
            $variationTemplate = '@WEBUI/'.$variant.'/StaticPages/'.$pTemplateName;
            if ($this->get('templating')->exists($variationTemplate)) {
                $this->logger->addWarning('Failed to load template: '.$variationTemplate);

                return $variationTemplate;
            }
        }
        $this->logger->addWarning('Failed to load template, using default: '.$defaultTemplateName);

        return $defaultTemplateName;
    }
}
