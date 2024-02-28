<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMApiBundle;

use Bazinga\Bundle\GeocoderBundle\Geocoder\LoggableGeocoder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\JsonArrayType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use FOS\UserBundle\Util\TokenGenerator;
use MMApiBundle\Entity\Meal;
use MMApiBundle\Entity\MealCategory;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\HttpFoundation\Request;

/**
 * MMMealSerach is a Mealmatch API service to provide search functionality for meals.
 *
 * @todo: A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class MMMealSearch
{
    /**
     * This service class uses the EntityManager.
     *
     * @var EntityManager
     */
    private $em;

    /**
     * This services class uses a geocoder (GMaps).
     *
     * @var LoggableGeocoder
     */
    private $geoCoder;

    /**
     * This service class writes log entries.
     *
     * @var Logger;
     */
    private $log;

    /**
     * Twig Templatig.
     *
     * @var TwigEngine
     */
    private $twig;

    public function __construct(EntityManager $pEM, LoggableGeocoder $pGeocoder, Logger $pLogger, TwigEngine $pTwig)
    {
        $this->em = $pEM;
        $this->geoCoder = $pGeocoder;
        $this->log = $pLogger;
        $this->twig = $pTwig;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param Request $pRequest
     *
     * @return mixed
     */
    public function search(Request $pRequest)
    {
        $qb = $this->createSearchQueryBuilder();
        $qb = $this->selectByStatus($qb);
        $qb = $this->selectByUserActive($qb);
        $searchCategories = $this->selectByCategories($pRequest, $qb);
        $searchLocation = $this->selectBySearchLocation($pRequest, $qb);

        list(
            $negativDateTime, $positiveDateTime
            ) =
            $this->selectByTime($pRequest, $qb);

        //
        // Create result data ...
        //
        $meals = $qb->getQuery()->getResult();

        /** @var ArrayCollection $lastSearchLocations */
        $lastSearchLocations = $this->createLastSearchLocations($pRequest, $meals);
        $mealLocations = $this->createMealLocations($meals);

        //
        // Normalize for json
        //
        $mealsJson = array();
        /** @var Meal $meal */
        foreach ($meals as $meal) {
            $normMeal['id'] = $meal->getId();
            $normMeal['title'] = $meal->getTitle();
            $normMeal['lat'] = $meal->getLatitude();
            $normMeal['lng'] = $meal->getLongitude();
            $normMeal['locationAddress'] = $meal->getLocationAddress();
            $normMeal['html'] =
                $this->twig->render(
                    '@MMWebFront/Meal/searchPanelWidget.html.twig',
                    array('meal' => $meal)
                );
            $locCount = $this->countLocation($meal->getLocationAddress(), $mealLocations);
            $this->log->addDebug('MultiMarker: count->'.$locCount);
            if ($locCount > 1) {
                $normMeal['locationMarker'] =
                    $this->twig->render(
                        '@API/Search/locationMultiMarker.html.twig',
                        array(
                            'loc' => $lastSearchLocations->get($meal->getLocationAddress())['hash'],
                            'locCount' => $locCount,
                            'locMeals' => $mealLocations->filter(
                                function ($m) use ($meal) {
                                    return $m['loc'] === $meal->getLocationAddress();
                                }
                            ),
                        )
                    );
            } else {
                $normMeal['locationMarker'] =
                    $this->twig->render(
                        '@API/Search/locationMarker.html.twig',
                        array(
                            'meal' => $meal,
                        )
                    );
            }
            array_push($mealsJson, $normMeal);
        }

        $sql = $qb->getQuery()->getDQL();
        $searchDateTime = $this->setSearchDateTime($pRequest);

        $searchPanel = array(
            'results' => \count($meals),
            'location' => $searchLocation,
            'categories' => $searchCategories,
            'datetime' => $searchDateTime,
            'modDatetime' => $negativDateTime->format('d.m.Y H:i'),
            'maxDatetime' => $positiveDateTime->format('d.m.Y H:i'),
            'sql' => $sql,
        );

        $adapter = new DoctrineORMAdapter($qb);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(5);

        return array(
            'meals' => $meals,
            'mealsJson' => $mealsJson,
            'searchPanel' => $searchPanel,
            'resultPager' => $pagerfanta,
        );
    }

    /**
     * Finds all Meal entities who match their Address.locationAddress (the google location).
     *
     * @param string $pHash the locationAddress to search for
     *
     * @return array|meal the found Meal entities in an array
     */
    public function findAllByLocationAddress(string $pHash, Request $pRequest)
    {
        $sess = $pRequest->getSession();
        if ($sess->has('lastSearchLocations')) {
            /** @var ArrayCollection $lastSearchLoc */
            /** @var JsonArrayType $lastJson */
            // $lastJson = json_decode($sess->get('lastSearchLocations'), true);
            $lastSearchLoc = new ArrayCollection($sess->get('lastSearchLocations'));
            foreach ($lastSearchLoc->toArray() as $lastLoc) {
                if ($lastLoc['hash'] === $pHash) {
                    $lastMeals = $lastLoc['meals'];
                }
            }
        } else {
            throw new \HttpException('Doh!');
        }

        $mealIDs = array();
        /** @var Meal $lastMeal */
        foreach ($lastMeals as $lastMeal) {
            array_push($mealIDs, $lastMeal->getId());
        }

        $qbByLocAddress = $this->em
            ->getRepository('MMApiBundle:Meal')
            ->createQueryBuilder('m')
            ->where('m.id IN(:last)')
            ->setParameter('last', array_values($mealIDs))
        ;

        return $qbByLocAddress->getQuery()->getResult();
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return QueryBuilder
     */
    private function createSearchQueryBuilder(): QueryBuilder
    {
        //
        // Build the query ...
        //
        /* @var QueryBuilder $qb */
        return $this->em->getRepository('MMApiBundle:Meal')->createQueryBuilder('m')
                        ->select('m')
                        ->join('m.address', 'a')
                        ->join('m.host', 'host')
                        ->andWhere('m.address IS NOT NULL')
            // ->andWhere('host.enabled IS TRUE')

            ;
    }

    /**
     * selects meal.status === RUNNING.
     *
     * @param QueryBuilder $qb the QueryBuilder to update with select
     *
     * @return QueryBuilder the updated QueryBuilder
     */
    private function selectByStatus(QueryBuilder $qb): QueryBuilder
    {
        return $qb->where('m.status LIKE :status')
                  ->setParameter('status', 'RUNNING')
            ;
    }

    /**
     * selects meal.host.enabled === TRUE.
     *
     * @param QueryBuilder $qb the QueryBuilder to update with select
     *
     * @return QueryBuilder the updated QueryBuilder
     */
    private function selectByUserActive(QueryBuilder $qb)
    {
        return $qb->andWhere('host.enabled LIKE :active')
                  ->setParameter('active', true)
            ;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param        $searchCategories
     * @param        $qb
     * @param string $myArgument       with a *description* of this argument, these may also
     *                                 span multiple lines
     *
     * @return mixed
     */
    private function selectByCategories(Request $pRequest, QueryBuilder $qb)
    {
        $searchCategories = $this->getValidMealCategoriesFromRequest($pRequest);
        //
        // Select search Categories if requested ...
        //
        if (isset($searchCategories[2])) {
            $qb->andWhere(
                ':cat1 MEMBER OF m.categories
            OR :cat2 MEMBER OF m.categories
            OR :cat3 MEMBER OF m.categories'
            );
            $qb->setParameter('cat1', $searchCategories[0]);
            $qb->setParameter('cat2', $searchCategories[1]);
            $qb->setParameter('cat3', $searchCategories[2]);
        } elseif (isset($searchCategories[1])) {
            $qb->andWhere(
                '
            :cat1 MEMBER OF m.categories
            OR :cat2 MEMBER OF m.categories
            '
            );
            $qb->setParameter('cat1', $searchCategories[0]);
            $qb->setParameter('cat2', $searchCategories[1]);
        } elseif (isset($searchCategories[0])) {
            $qb->andWhere(
                '
            :cat1 MEMBER OF m.categories
            '
            );
            $qb->setParameter('cat1', $searchCategories[0]);
        }
        // nothing to do ;)

        return $searchCategories;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param        $searchCategoryFromReq
     * @param string $myArgument            with a *description* of this argument, these may also
     *                                      span multiple lines
     *
     * @return array
     */
    private function getValidMealCategoriesFromRequest(Request $pRequest): array
    {
        $searchCategoryFromReq = $pRequest->get('searchCategory');
        $searchCategories = array();
        // Explode categories from request by ","
        $searchCatReqArr = explode(',', $searchCategoryFromReq);
        foreach ($searchCatReqArr as $searchCat) {
            $searchCategory =
                $this->em->getRepository('MMApiBundle:MealCategory')
                         ->findOneBy(
                             array('name' => $searchCat)
                         )
            ;
            if ($searchCategory instanceof MealCategory) {
                array_push($searchCategories, $searchCategory);
            }
        }

        return $searchCategories;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param Request      $pRequest
     * @param QueryBuilder $pQueryBuilder
     *
     * @return string
     */
    private function selectBySearchLocation(Request $pRequest, QueryBuilder $pQueryBuilder): string
    {
        $searchLocation = $pRequest->get('searchLocation');
        //
        // Select search location if requested ...
        //
        if (isset($searchLocation)) {
            $locationOK = false;
            $qbCity = $this->em->getRepository('MMApiBundle:Address')->createQueryBuilder('a');
            $qbCity->select('a.city');
            $qbCity->groupBy('a.city');
            $resultArray = $qbCity->getQuery()->getResult();
            foreach ($resultArray as $result) {
                if ($result['city'] === $searchLocation) {
                    $locationOK = true;
                }
            }
            if ($locationOK) {
                $pQueryBuilder->join('m.address', 'a2');
                $pQueryBuilder->andWhere('a2.city LIKE :city');
                $pQueryBuilder->setParameter('city', $searchLocation);
            } else {
                $searchLocation = 'Überall';
            }
        } else {
            $searchLocation = 'Überall';
        }

        return $searchLocation;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param              $searchDateTimeMod
     * @param              $searchDateTimeMax
     * @param              $searchDateTime
     * @param QueryBuilder $qb
     * @param string       $myArgument        with a *description* of this argument, these may also
     *                                        span multiple lines
     *
     * @return array
     */
    private function selectByTime(Request $pRequest, QueryBuilder $qb): array
    {
        $searchDateTimeMod = $pRequest->get('datetimeMod');
        $searchDateTimeMax = $pRequest->get('datetimeMax');
        $searchDateTime = $this->setSearchDateTime($pRequest);
        //
        // Select Time variables ...
        //
        if (isset($searchDateTimeMod)) {
            // get min and max time values
            $negativDateTime = new \DateTime($searchDateTimeMod);
            $positiveDateTime = new \DateTime($searchDateTimeMax);
        } else {
            // DateTime selection from request
            $negativeDateTimeModifier = '-150 minutes';
            if ('now' === $searchDateTime) {
                $positiveDateTimeModifier = '+3 days';
            } else {
                $positiveDateTimeModifier = '+150 minutes';
            }

            // get min and max time values
            $negativDateTime = new \DateTime($searchDateTime);
            $negativDateTime->modify($negativeDateTimeModifier);
            $positiveDateTime = new \DateTime($searchDateTime);
            $positiveDateTime->modify($positiveDateTimeModifier);
        }

        $qb->andWhere('m.startDateTime > :negDateTime');
        $qb->setParameter('negDateTime', $negativDateTime);
        $qb->orderBy('m.startDateTime', 'ASC');

        return array($negativDateTime, $positiveDateTime);
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param        $searchDateTime
     * @param string $myArgument     with a *description* of this argument, these may also
     *                               span multiple lines
     *
     * @return string
     */
    private function setSearchDateTime(Request $pRequest): string
    {
        $searchDateTime = $pRequest->get('datetime');

        if (!isset($searchDateTime)
            ||
            '' === $searchDateTime
        ) {
            return 'now';
        }

        return $searchDateTime;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param Request $pRequest
     * @param         $meals
     * @param string  $myArgument with a *description* of this argument, these may also
     *                            span multiple lines
     *
     * @return ArrayCollection
     */
    private function createLastSearchLocations(Request $pRequest, $meals): ArrayCollection
    {
        // Put mealLocations by "loc" into session with "hash"
        $lastSearchLocations = new ArrayCollection();
        $tokenG = new TokenGenerator();
        /** @var Meal $meal */
        foreach ($meals as $meal) {
            $loc = $meal->getLocationAddress();
            if ($lastSearchLocations->containsKey($loc)) {
                // add to it ...
                $location = $lastSearchLocations->get($loc);
                array_push($location['meals'], $meal);
                ++$location['count'];
                $lastSearchLocations->set($loc, $location);
            } else {
                // first entry
                $lastSearchLocations->set(
                    $loc,
                    array(
                        'loc' => $loc,
                        'hash' => $tokenG->generateToken(),
                        'meals' => array($meal),
                        'count' => 1,
                    )
                );
            }
        }

        $pRequest->getSession()->set('lastSearchLocations', $lastSearchLocations);

        return $lastSearchLocations;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param        $meals
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return ArrayCollection
     */
    private function createMealLocations($meals): ArrayCollection
    {
        //
        // MultiMarker ...
        //
        $this->log->addDebug('MultiMarker: All->'.implode(',', $meals));
        $mealLocations = new ArrayCollection();
        /** @var Meal $meal */
        foreach ($meals as $meal) {
            $mealLocations->add(
                array(
                    'id' => $meal->getId(),
                    'meal' => $meal->toJSON(),
                    'loc' => $meal->getLocationAddress(),
                )
            );
        }

        return $mealLocations;
    }

    /**
     * Counts the occurences of ['loc'] === $pLoc in the specified Collection
     * and returns the value.
     *
     * @param string          $pLoc        the "loc" to count
     * @param ArrayCollection $pCollection the collection to search in
     *
     * @return int the number of found matches
     */
    private function countLocation(string $pLoc, ArrayCollection $pCollection)
    {
        return $pCollection->filter(
            function ($entry) use ($pLoc) {
                return $entry['loc'] === $pLoc;
            }
        )->count()
            ;
    }
}
