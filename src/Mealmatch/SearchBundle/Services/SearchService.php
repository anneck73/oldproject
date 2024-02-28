<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\SearchBundle\Services;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Entity\Meal\BaseMeal;
use Mealmatch\ApiBundle\Entity\Meal\HomeMeal;
use Mealmatch\ApiBundle\Entity\Meal\MealOffer;
use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Monolog\Logger;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;

class SearchService
{
    /**
     * This service class uses the EntityManager.
     *
     * @var EntityManager
     */
    private $entityManager;

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

    /**
     * @todo: Finish PHPDoc!
     *
     * @var Serializer
     */
    private $serializer;

    /**
     * SearchService constructor.
     *
     * @param EntityManager $pEM
     * @param Logger        $pLogger
     * @param TwigEngine    $pTwig
     */
    public function __construct(
        Logger $pLogger,
        EntityManager $pEM,
        TwigEngine $pTwig,
        Serializer $serializer
    ) {
        $this->entityManager = $pEM;
        $this->log = $pLogger;
        $this->twig = $pTwig;
        $this->serializer = $serializer;
    }

    /**
     * Searches for Meals using HttpRequest and always returns the results in an array.
     *
     * This search method encapsulates all search logic always returning a result using the following result array:
     * [
     *  'mealMarker' => $mealsJson,
     *  'meals' => $meals,
     *  'mealsJson' => $mealsJson,
     *  'searchPanel' => $searchPanel,
     *  'resultPager' => $pagerfanta,
     *  ].
     *
     * It uses the HttpRequest to gather search criteria.
     *
     * @param Request $request
     * @param mixed   $mealType
     *
     * @throws \Pagerfanta\Exception\NotIntegerMaxPerPageException
     * @throws \Pagerfanta\Exception\LessThan1MaxPerPageException
     * @throws \RuntimeException
     *
     * @return array
     */
    public function search(Request $request, $mealType = 'BaseMeal'): array
    {
        // search all Meal-Types: ProMeal, HomeMeal ... simply search for basemeal.
        $searchCriteria = $this->extractSearchCriteria($request);

        $searchCriteria = array_merge(
            $searchCriteria,
            array(
                'mealType' => $mealType,
                'leaf' => 1,
            )
        );

        $searchResults = $this->doQuery($searchCriteria);

        $mealsJson = $this->createJSONResults($searchResults);
        // pagerFanta
        $adapter = new ArrayAdapter($mealsJson);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(5);
        $page = $request->get('page', 1);
        $pagerfanta->setCurrentPage($page);
        $currentPageResults = $pagerfanta->getCurrentPageResults();
        // provide view data as array
        $resultArray = array(
            'mealMarker' => $mealsJson,
            'meals' => $searchResults,
            'mealsJSON' => $currentPageResults,
            'searchPanel' => array(),
            'resultPager' => $pagerfanta,
            'search' => $searchCriteria,
        );

        return $resultArray;
    }

    public function searchPro(Request $request): array
    {
        // Extracting search criteria from request ...
        $searchCriteria = $this->extractSearchCriteria($request);

        // Adding defaults ...
        $searchCriteria = array_merge(
            $searchCriteria,
            array(
                // only ProMeals
                'mealType' => 'ProMeal',
                // only leafs, e.g. no root-Meal's
                'leaf' => 1,
            )
        );

        // do the Query with criteria
        $searchResults = $this->doQuery($searchCriteria);

        // extract JSONResults
        $mealsJson = $this->createJSONResults($searchResults);
        //$mealsJson = $this->createJSONResults($searchResults);

        // pagerFanta for searchResults
        $adapter = new ArrayAdapter($searchResults);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(10);
        $page = $request->get('page', 1);
        $pagerfanta->setCurrentPage($page);
        $currentPageResults = $pagerfanta->getCurrentPageResults();

        // provide view data as array
        $resultArray = array(
            'meals' => $searchResults,
            'mealsJSON' => $currentPageResults,
            'searchPanel' => array(),
            'resultPager' => $pagerfanta,
        );

        return $resultArray;
    }

    /**
     * @todo: Finish PHPDoc!
     * @todo: Not tested yet, should(!) work ...
     *
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param BaseMeal $baseMeal the meal to calculate the distance to
     * @param Point    $point    the from point
     *
     * @return mixed
     */
    public function getDistance(BaseMeal $baseMeal, Point $point)
    {
        $mealAddress = $baseMeal->getAddress();

        return $this->entityManager->getRepository('ApiBundle:Meal\MealAddress')
            ->createQueryBuilder('mealAddress')
            ->andWhere('DISTANCE(mealAddress.location, :latitude, :longitude)')
            ->setParameter('latitude', $point->getLatitude())
            ->setParameter('longitude', $point->getLongitude())
            ->getQuery()
            ->execute();
        // $this->entityManager->getRepository('ApiBundle:Meal\MealAddress')->findByDistance();
    }

    public function searchByCriteria(array $searchCriteria): array
    {
        return $this->doQuery($searchCriteria);
    }

    /**
     * @todo: No implemented yet!!!!
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param Request $request
     * @param string  $myArgument with a *description* of this argument, these may also
     *                            span multiple lines
     *
     * @return array
     */
    private function extractSearchCriteria(Request $request): array
    {
        $getParams = $request->query->all();

        return array_merge($getParams,
            array(
                'city' => $request->get('searchLocation'),
            )
        );
    }

    private function encode(array $results): Collection
    {
        $serCollection = new ArrayCollection();
        foreach ($results as $result) {
            $ser = $this->serializer->serialize($result, 'json');
            $serCollection->add($ser);
        }

        return $serCollection;
    }

    private function doQuery(array $searchCriteria): array
    {
        $entityName = $this->extractEntityName($searchCriteria);

        // If there is a city criteria, we filterByCity
        if (!empty($searchCriteria['city'])) {
            $city = $searchCriteria['city'];
        }
        // or country
        if (!empty($searchCriteria['country'])) {
            $country = $searchCriteria['country'];
        }
        // or subLocatlity
        if (!empty($searchCriteria['sublocality'])) {
            $sublocality = $searchCriteria['sublocality'];
        }

        // Now Search Criteria is RESETTET!!! OVERWRITTEN
        // @todo: think of a more elegant solution!
        // only search for running, leaf's ...
        $searchCriteria = array_merge($searchCriteria,
            array(
                'status' => ApiConstants::MEAL_STATUS_RUNNING,
                'leaf' => 1,
            ));

        $all = null;
        // searching for all using the extracted entity name !!!
        try {
            $all = $this->entityManager->getRepository($entityName)->findBy(
                array(
                    'status' => ApiConstants::MEAL_STATUS_RUNNING,
                    'leaf' => 1,
                )
            );
            // $all = $this->dbQuery($entityName, $searchCriteria);
            $this->log->addDebug(
                'Found '.\count($all).'('.$entityName.')'.
                ' using searchCriteria: '.json_encode($searchCriteria)
            );
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage(),
                array('Exception' => $exception,
                    'SearchCriteria' => json_encode($searchCriteria),
                )
            );
        }

        // If there is a city criteria, we filterByCity
        if (!empty($city)) {
            $all = $this->filterByAddressKey($all, 'City', $city);
            $this->log->addDebug('after FilterByCity: '.\count($all));
        }

        // or country
        if (!empty($country)) {
            $all = $this->filterByAddressKey($all, 'CountryCode', $country);
            $this->log->addDebug('after FilterByCountry: '.\count($all));
        }

        // or Sublocality ...
        if (!empty($sublocality)) {
            $all = $this->filterByAddressKey($all, 'SubLocality', $sublocality);
            $this->log->addDebug('after FilterBySublocality: '.\count($all));
        }

        // or searchTerm ...
        if (!empty($searchCriteria['searchTerm'])) {
            try {
                $all = $this->filterBySearchTerm($all, $searchCriteria['searchTerm']);
                $this->log->addDebug('after FilterBySearchTerm: '.\count($all));
            } catch (\Throwable $throwable) {
                $this->log->addError('Failed to filterBySearchTerm! Skipped filtering!',
                    array(
                        'Message' => $throwable->getMessage(),
                    )
                );
            }
        }

        // dont show startDate from yesterday ...
        $all = $this->filterBySmallerThanDateTime($all, new DateTime('now'));

        // sort by startDate
        $all = $this->sortByStartDate($all);

        // return filtered, and sorted result array.
        return $all;
    }

    private function createJSONResults(array $searchResults): array
    {
        //
        // Normalize for json
        //
        $mealsJson = array();
        /** @var BaseMeal $meal */
        foreach ($searchResults as $meal) {
            if ($meal->getAddress()->isGeoCoded()) {
                $normMeal['id'] = $meal->getId();
                $normMeal['title'] = $meal->getTitle();
                $normMeal['lat'] = $meal->getAddress()->getLatitude();
                $normMeal['lng'] = $meal->getAddress()->getLongitude();
                $normMeal['locationAddress'] = $meal->getAddress()->getLocationString();

                $normMeal['html'] =
                    $this->twig->render(
                        '@WEBUI/Search/gmaps/mealResult-Card-'.$meal->getMealType().'.html.twig',
                        array(
                            'meal' => $meal,
                            'location_marker' => false,
                        )
                    );
                $normMeal['html_xs'] =
                    $this->twig->render(
                        '@WEBUI/Search/gmaps/xs/mealResult-Card-'.$meal->getMealType().'.html.twig',
                        array('meal' => $meal)
                    );
                $normMeal['locationMarker'] =
                    $this->twig->render(
                        '@WEBUI/Search/gmaps/mealResult-Card-'.$meal->getMealType().'.html.twig',
                        array(
                            'meal' => $meal,
                            'location_marker' => true,
                        )
                    );

                array_push($mealsJson, $normMeal);
            }
        }

        return $mealsJson;
    }

    /**
     * Helper to extract the entityName from the searchCriteria.
     *
     * It searches for an ARRAY-KEY: 'mealType' and returns 'ApiBundle:Meal\BaseMeal' if no VALUE is found.
     *  Value: 'ProMeal' will return ApiBundle:Meal\ProMeal
     *  Value: 'HomeMeal' will return ApiBundle:Meal\HomeMeal
     *  Value: 'BaseMeal' will return ApiBundle:Meal\BaseMeal
     *
     * @param array $searchCriteria the array to be searched in order to extract the entityName
     *
     * @return string the entityName in the form of BUNDLE:NAMESPACE
     */
    private function extractEntityName(array &$searchCriteria): string
    {
        if (!isset($searchCriteria['mealType'])) {
            return 'ApiBundle:Meal\BaseMeal';
        }

        switch ($searchCriteria['mealType']) {
            case 'ProMeal':
                $entityName = 'ApiBundle:Meal\ProMeal';
                break;
            case 'HomeMeal':
                $entityName = 'ApiBundle:Meal\HomeMeal';
                break;
            default:
                $entityName = 'ApiBundle:Meal\BaseMeal';
                break;
        }

        unset($searchCriteria['mealType']);

        return $entityName;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param $all
     * @param $searchTerm
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return array
     */
    private function filterBySearchTerm($all, $searchTerm): array
    {
        $resultC = new ArrayCollection($all);
        $resultC = $resultC->filter(
            function (BaseMeal $meal) use ($searchTerm) {
                $titleMatch = preg_match('/'.$searchTerm.'/i', $meal->getTitle()) >= 1;
                $descriptionMatch = preg_match('/'.$searchTerm.'/i', $meal->getDescription()) >= 1;

                $matches = array($titleMatch, $descriptionMatch);
                // ProMeal Specifics search added to matches[]
                if ($meal instanceof ProMeal) {
                    /** @var MealOffer $offer */
                    foreach ($meal->getMealOffers() as $offer) {
                        $offerNameMatches[$offer->getId()] = preg_match('/'.$searchTerm.'/i', $offer->getName()) >= 1;
                        $offerDescMatches[$offer->getId()] = preg_match('/'.$searchTerm.'/i', $offer->getDescription()) >= 1;
                    }
                    // if we find one match, its true.
                    $matches[] = array_search(true, $offerNameMatches, true);
                    $matches[] = array_search(true, $offerDescMatches, true);
                    // matching if string pos > 1
                    $matches[] = preg_match('/'.$searchTerm.'/i', $meal->getHost()->getRestaurantProfile()->getName()) >= 1;
                    $matches[] = preg_match('/'.$searchTerm.'/i', $meal->getHost()->getRestaurantProfile()->getDescription()) >= 1;
                }
                if ($meal instanceof HomeMeal) {
                    $matches[] = preg_match('/'.$searchTerm.'/i', $meal->getMealMain()) >= 1;
                    $matches[] = preg_match('/'.$searchTerm.'/i', $meal->getMealStarter()) >= 1;
                    $matches[] = preg_match('/'.$searchTerm.'/i', $meal->getMealDesert()) >= 1;
                }
                // if we find one match, its true.
                $result = array_search(true, $matches, true);
                // return TRUE only if result IS NOT exactly FALSE (e.g. int or string)
                return !(false === $result);
            }
        );

        return $resultC->toArray();
    }

    private function filterByAddressKey($all, $addressKey, $filterBy): array
    {
        $resultC = new ArrayCollection($all);

        $resultC = $resultC->filter(
            function (BaseMeal $meal) use ($addressKey, $filterBy) {
                $address = $meal->getAddress();
                $addrMirror = new \ReflectionMethod('Mealmatch\ApiBundle\Entity\Meal\MealAddress', 'get'.$addressKey);
                // if the regexp finds more or at least 1, we are good
                return preg_match('/'.$filterBy.'/i', $addrMirror->invoke($address)) >= 1;
            }
        );

        return $resultC->toArray();
    }

    private function filterBySmallerThanDateTime($all, DateTime $dateTime): array
    {
        $resultC = new ArrayCollection($all);
        $resultC = $resultC->filter(
            function (BaseMeal $meal) use ($dateTime) {
                return $meal->getStartDateTime() > $dateTime;
            }
        );

        return $resultC->toArray();
    }

    private function sortByStartDate($all): array
    {
        $resultC = new ArrayCollection($all);
        $resultIt = $resultC->getIterator();
        $resultIt->uasort(
            function (BaseMeal $a, BaseMeal $b) {
                return ($a->getStartDateTime() < $b->getStartDateTime()) ? -1 : 1;
            }
        );

        return iterator_to_array($resultIt);
    }

    private function dbQuery($entityName, $searchCriteria)
    {
        /** @var QueryBuilder $queryB */
        $queryB = $this->entityManager
            ->getRepository("ApiBundle:Meal\ProMeal")
            ->createQueryBuilder('m')
            ->select('m')
            ->join('m.mealAddresses', 'mealAddress')
            ->leftJoin('m.mealOffers', 'mealOffers')
            ->leftJoin('m.categories', 'mealCategories')
            ->leftJoin('m.host', 'mealHost');

        if (!empty($searchCriteria['city'])) {
            $queryB->andWhere('mealAddress.city = :searchCity');
            $queryB->setParameter('searchCity', $searchCriteria['city']);
        }

        if (!empty($searchCriteria['searchTerm'])) {
            $searchTerm = $searchCriteria['searchTerm'];
            $searchTermExp = $queryB->expr()->orX(
                $queryB->expr()->like('mealOffers.name', ':searchTerm'),
                $queryB->expr()->like('mealOffers.description', ':searchTerm'),
                $queryB->expr()->like('m.title', ':searchTerm'),
                $queryB->expr()->like('m.description', ':searchTerm'),
                $queryB->expr()->like('m.tableTopic', ':searchTerm')
            );
            $queryB->andWhere($searchTermExp);
            $queryB->setParameter('searchTerm', $searchTerm);
        }

        if (!empty($searchCriteria['mealCategory'])) {
            $queryB->andWhere('mealCategories.name = :searchCategory');
            $queryB->setParameter('searchCategory', $searchCriteria['mealCategory']);
        }
        $this->log->addDebug($queryB->getQuery()->getDQL());

        return $queryB->getQuery()->getResult();
    }
}
