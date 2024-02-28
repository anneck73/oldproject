<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\ORM\QueryBuilder;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Entity\Meal\BaseMeal;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
use Mealmatch\ApiBundle\Entity\Meal\HomeMeal;
use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Mealmatch\ApiBundle\Exceptions\MealmatchException;
use Mealmatch\ApiBundle\Exceptions\ServiceDataException;
use Mealmatch\ApiBundle\Exceptions\ServiceDataValidationException;
use Mealmatch\ApiBundle\Model\ProMealServiceData;
use MMUserBundle\Entity\MMUser;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

/**
 * The ProMealService deals with "all things" regarding ProMeals.
 */
class ProMealService extends AbstractFinderService implements ProMealServiceInterface
{
    /**
     * The logger used.
     *
     * @var Logger
     */
    private $logger;

    /**
     * The entity manager.
     *
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Translations.
     *
     * @var Translator
     */
    private $translator;

    /**
     * Internal Business-Data-Model features.
     *
     * @var ProMealServiceData
     */
    private $dataModel;

    /**
     * Used to convert RestaurantAddress to MealAddress.
     *
     * @var GeoAddressService
     */
    private $geoAddressService;
    /**
     * Using the restaurantservice.
     *
     * @var RestaurantService
     */
    private $restaurantService;

    /**
     * ProMealService constructor.
     *
     * @param Logger            $logger
     * @param EntityManager     $entityManager
     * @param Translator        $translator
     * @param GeoAddressService $geoAddressService
     * @param RestaurantService $restaurantService
     */
    public function __construct(
        Logger $logger,
        EntityManager $entityManager,
        Translator $translator,
        GeoAddressService $geoAddressService,
        RestaurantService $restaurantService
    ) {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->dataModel = new ProMealServiceData();
        $this->geoAddressService = $geoAddressService;
        $this->restaurantService = $restaurantService;
    }

    /**
     * Puts the Guest of the BaseMealTicket into the BaseMeal, if the BaseMealTicket "status" equals
     * ApiConstants::MEAL_TICKET_STATUS_PAYED.
     *
     *
     * @param BaseMealTicket $mealTicket
     *
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws MealmatchException                    if the $mealTicket is a ProMeal!
     *
     * @return ProMealServiceData
     */
    public function joinMeal(BaseMealTicket $mealTicket): ProMealServiceData
    {
        $proMSD = new ProMealServiceData();
        $proMSD->setValidity(false);
        $baseMeal = $mealTicket->getBaseMeal();

        if ($baseMeal instanceof ProMeal) {
            throw new MealmatchException('Only HomeMeals can use JoinRequests!');
        }

        if ($baseMeal instanceof HomeMeal) {
            $proMSD->setHomeMeal($baseMeal);
        }

        if (null !== $baseMeal && $baseMeal->getGuests()->contains($mealTicket->getGuest())) {
            $proMSD->setValidity(true);
            $this->logger->addWarning('ProMealService:joinMeal->addGuest('.$mealTicket->getGuest()->getId().')'
                .' is already a guest in BaseMeal('.$baseMeal->getId().')');

            return $proMSD;
        }

        if (null !== $baseMeal && ApiConstants::MEAL_TICKET_STATUS_PAYED === $mealTicket->getStatus()) {
            $this->logger->addDebug('ProMealService:joinMeal->addGuest('.$mealTicket->getGuest()->getId().')'
            .' to BaseMeal('.$baseMeal->getId().')');
            $baseMeal->addGuest($mealTicket->getGuest());
            $this->entityManager->persist($baseMeal);
            $this->entityManager->flush();
            if ($baseMeal instanceof ProMeal) {
                $proMSD->setProMeal($baseMeal);
            }
            $proMSD->setValidity(true);
        }

        return $proMSD;
    }

    /**
     * Creates a new ProMeal entity contained in the ProMealServiceData-Result.
     *
     * @param ProMeal $proMeal
     * @param MMUser  $MMUser
     *
     * @return ProMealServiceData
     */
    public function createFromEntityWithHost(ProMeal $proMeal, MMUser $MMUser): ProMealServiceData
    {
        $this->logger->addDebug(
            sprintf('->%s using %s %s', __METHOD__, $proMeal, $MMUser)
        );

        // A new ProMeal requires a valid RestaurantProfile.
        // As of today only with 100% filled.
        $restaurantProfile = $MMUser->getRestaurantProfile();

        // Adding the user as host
        $proMeal->setHost($MMUser);

        // Adding the default category
        // @todo: there must be a better way to get the "default" category.
        $defaultCategory = $this->entityManager->getRepository('ApiBundle:Meal\BaseMealCategory')->findAll()[0];
        $categories = new ArrayCollection(array($defaultCategory));
        $proMeal->setCategories($categories);

        // Adding the restaurant address to the pro meal.
        $restaurantAddress = $restaurantProfile->getAddress();
        $mealAddress = $this->geoAddressService->copyToMealAddress($restaurantAddress);
        $proMeal->addMealAddress($mealAddress);

        return $this->createFromEntity($proMeal);
    }

    /**
     * Creates the ProMealServiceData using a new ProMeal entity, persist it and puts the updated entity into the
     * returned ServiceData. No validation is applied!
     *
     * @return ProMealServiceData
     */
    public function createFromEntity(ProMeal $proMeal): ProMealServiceData
    {
        try {
            // re-insert this $proMeal into the service data
            $this->dataModel->setProMeal($proMeal);
            // persist the entity
            $this->entityManager->persist($proMeal);
            $this->entityManager->flush($proMeal);
            $this->logger->addInfo('Created ProMeal: '.$proMeal->getId());
        } catch (ORMInvalidArgumentException $ORMInvalidArgumentException) {
            $this->logger->addError($ORMInvalidArgumentException->getMessage());
            $this->dataModel->addError($ORMInvalidArgumentException->getMessage());
        } catch (ORMException $ORMException) {
            $this->logger->addError($ORMException->getMessage());
            $this->dataModel->addError($ORMException->getMessage());
        } catch (ServiceDataValidationException $dataValidationException) {
            $this->logger->addError($dataValidationException->getMessage());
            $this->dataModel->addError($dataValidationException->getMessage());
        } catch (ServiceDataException $dataException) {
            $this->logger->addError($dataException->getMessage());
            $this->dataModel->addError($dataException->getMessage());
        }

        return $this->dataModel;
    }

    /**
     * The Name of the Entity this Service uses.
     *
     * @return string
     */
    public function getEntityName(): string
    {
        return ApiConstants::ENTITY_PRO_MEAL;
    }

    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    public function restore(int $id): ProMealServiceData
    {
        $proMeal = $this->restoreProMeal($id);
        $this->dataModel->setProMeal($proMeal);

        return $this->dataModel;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param int    $id
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @throws MealmatchException
     *
     * @return ProMeal
     */
    public function restoreProMeal(int $id): ProMeal
    {
        $found = $this->getEntityManager()->getRepository($this->getEntityName())->find($id);
        if (null !== $found && $found instanceof ProMeal) {
            return $found;
        }
        // @todo: this should be a different (better fit) exception ... find a better one!
        throw new MealmatchException("ID: '$id' is not valid!");
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
     * @return array
     */
    public function getTree(ProMeal $proMeal): array
    {
        $this->logger->addDebug(sprintf('->%s', __METHOD__));

        return $this->entityManager->getRepository(ApiConstants::ENTITY_PRO_MEAL)->getFlatTree('/'.$proMeal->getId());
    }

    public function isValid(ProMeal $proMeal): bool
    {
        $this->logger->addDebug(
            sprintf('->%s using %s', __METHOD__, $proMeal)
        );

        $this->dataModel->setProMeal($proMeal);
        // Validation exception is only logged ...
        try {
            $this->dataModel->validate();
        } catch (ServiceDataValidationException $serviceDataValidationException) {
            $this->logger->addAlert($serviceDataValidationException->getMessage());
        }
        // return boolean

        return $this->dataModel->isValid();
    }

    public function findAllByCity($city = 'Köln'): array
    {
        /** @var QueryBuilder $queryB */
        $queryB = $this->entityManager
            ->getRepository(ApiConstants::ENTITY_PRO_MEAL)
            ->createQueryBuilder('m')
            ->select('m')
            ->join('m.mealAddresses', 'mealAddress')
            ->leftJoin('m.mealOffers', 'mealOffers')
            ->leftJoin('m.categories', 'mealCategories')
            ->leftJoin('m.host', 'mealHost')
            ->andWhere('m.leaf = :leaf')
            ->setParameter('leaf', 1)
            ->andWhere('m.status = :status')
            ->setParameter('status', ApiConstants::MEAL_STATUS_RUNNING)
            ->andWhere('mealAddress.city = :searchCity')
            ->setParameter('searchCity', $city);
        // return as array not object tree (too big)
        return $queryB->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

    public function findAllByCityAndStartdate($city = 'Köln', \DateTime $dateTime = null): array
    {
        if (null === $dateTime) {
            $dateTime = new \DateTime('today');
        }
        /** @var QueryBuilder $queryB */
        $queryB = $this->entityManager
            ->getRepository(ApiConstants::ENTITY_PRO_MEAL)
            ->createQueryBuilder('m')
            ->select('m')
            ->join('m.mealAddresses', 'mealAddress')
            ->leftJoin('m.mealEvents', 'mealEvents')
            ->leftJoin('m.mealOffers', 'mealOffers')
            ->leftJoin('m.categories', 'mealCategories')
            ->leftJoin('m.host', 'mealHost')

            ->andWhere('mealEvents.startDateTime >= :dateTime')
            ->setParameter('dateTime', $dateTime)

            ->andWhere('m.status = :status')
            ->setParameter('status', ApiConstants::MEAL_STATUS_RUNNING)

            ->andWhere('m.leaf = :leaf')
            ->setParameter('leaf', 1)

            ->andWhere('mealAddress.city = :searchCity')
            ->setParameter('searchCity', $city);

        // return as array not object tree (too big)
        return $queryB->getQuery()->getResult();
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $tableTopic
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return array
     */
    public function findAllByTableTopic(string $tableTopic): array
    {
        return array_unique($this->entityManager->getRepository(ApiConstants::ENTITY_PRO_MEAL)->findBy(
            array('tableTopic' => $tableTopic, 'leaf' => 1, 'status' => ApiConstants::MEAL_STATUS_RUNNING)
        ));
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $restaurantName
     * @param string $tableTopic
     * @param string $startDateString
     * @param string $myArgument      with a *description* of this argument, these may also
     *                                span multiple lines
     *
     * @return array
     */
    public function findOneByRTD(string $restaurantName, string $tableTopic, string $startDateString): array
    {
        $restaurant = $this->entityManager->getRepository('MMUserBundle:MMRestaurantProfile')->findOneBy(
          array('name' => $restaurantName)
        );

        $allMeals = $this->entityManager->getRepository(ApiConstants::ENTITY_PRO_MEAL)->findBy(
            array('tableTopic' => $tableTopic, 'leaf' => 1, 'status' => ApiConstants::MEAL_STATUS_RUNNING,
                'host' => $restaurant, )
        );

        $startDate = new \DateTime($startDateString);

        return $this->filterByMatchingDate($allMeals, $startDate);
    }

    public function findByTitle($mealTitle)
    {
        return $this->entityManager->getRepository('ApiBundle:Meal\ProMeal')->findBy(
            array(
                'title' => $mealTitle,
                'leaf' => 1,
            )
        );
    }

    public function findAllBy(array $criteria)
    {
        return $this->entityManager->getRepository('ApiBundle:Meal\ProMeal')->findBy(
            $criteria
        );
    }

    public function findRunningByCategory($category)
    {
        throw new \BadMethodCallException('This has not been implemented yet!');
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param $all
     * @param \DateTime $date
     * @param string    $myArgument with a *description* of this argument, these may also
     *                              span multiple lines
     *
     * @return array
     */
    private function filterByMatchingDate($all, \DateTime $date): array
    {
        $resultC = new ArrayCollection($all);
        $resultC = $resultC->filter(
            function (BaseMeal $meal) use ($date) {
                if (
                    $meal->getStartDateTime() === $date
                ) {
                    return false;
                }

                return true;
            }
        );

        return $resultC->toArray();
    }
}
