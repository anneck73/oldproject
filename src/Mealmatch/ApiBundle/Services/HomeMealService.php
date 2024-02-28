<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Entity\Meal\HomeMeal;
use Mealmatch\ApiBundle\Entity\Meal\MealAddress;
use Mealmatch\ApiBundle\Model\HomeMealServiceData;
use MMUserBundle\Entity\MMUser;
use Monolog\Logger;
use PhpSpec\Exception\Exception;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

/**
 * The ProMealService "serves" data to the requesting classes, e.g.: controllers, etc.
 */
class HomeMealService extends AbstractFinderService implements HomeMealServiceInterface
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
     * @var HomeMealServiceData
     */
    private $dataModel;

    /**
     * MealService constructor.
     *
     * @param Logger        $logger
     * @param EntityManager $entityManager
     * @param Translator    $translator
     */
    public function __construct(Logger $logger, EntityManager $entityManager, Translator $translator)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->dataModel = new HomeMealServiceData();
    }

    /**
     * Creates the HomeMealServiceData using the ProMeal entity.
     *
     * @return HomeMealServiceData
     */
    public function createFromEntity(HomeMeal $homeMeal): HomeMealServiceData
    {
        try {
            $this->entityManager->persist($homeMeal);
            $this->entityManager->flush($homeMeal);
            $this->entityManager->refresh($homeMeal);
            $this->dataModel->setHomeMeal($homeMeal);
            $this->dataModel->validate();
            $this->logger->addInfo('Created HomeMeal: '.$this->dataModel);
        } catch (ORMInvalidArgumentException $ORMInvalidArgumentException) {
            $this->logger->addError($ORMInvalidArgumentException->getMessage());
            $this->dataModel->addError($ORMInvalidArgumentException->getMessage());
        } catch (ORMException $ORMException) {
            $this->logger->addError($ORMException->getMessage());
            $this->dataModel->addError($ORMException->getMessage());
        } catch (Exception $exception) {
            $this->logger->addError($exception->getMessage());
            $this->dataModel->addError($exception->getMessage());
        }

        return $this->dataModel;
    }

    public function getEntityName(): string
    {
        return ApiConstants::ENTITY_HOME_MEAL;
    }

    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * Validates the HomeMeal and puts the Results into HomeMealServiceData.
     *
     * @param HomeMeal $homeMeal
     *
     * @return HomeMealServiceData
     */
    public function validate(HomeMeal $homeMeal): HomeMealServiceData
    {
        $this->dataModel->setHomeMeal($homeMeal);
        $this->dataModel->validate();

        return $this->dataModel;
    }

    /**
     * Validates the HomeMeal and returns true if valid.
     *
     * @param HomeMeal $homeMeal
     *
     * @return bool true if valid, else false
     */
    public function isValid(HomeMeal $homeMeal): bool
    {
        $this->dataModel->setHomeMeal($homeMeal);
        $this->dataModel->validate();

        return $this->dataModel->isValid();
    }

    /**
     * Creates a new HomeMeal with the specified MMUser as the host (createdBy).
     *
     * @param HomeMeal $homeMeal the HomeMeal to use as input for creation
     * @param MMUser   $MMUser   the MMUser to become the host of the meal
     *
     * @return HomeMealServiceData contains the result of the operation
     */
    public function createFromEntityWithHost(HomeMeal $homeMeal, MMUser $MMUser): HomeMealServiceData
    {
        $this->logger->addDebug(
            sprintf('->%s using %s %s', __METHOD__, $homeMeal, $MMUser)
        );

        // Adding the user as host
        $homeMeal->setHost($MMUser);

        // create the default address using the homeMeal->Host->Profile informations.
        $hostProfileAddress = new MealAddress();
        $hostAddrLine1 = $homeMeal->getHost()->getProfile()->getAddressLine1();
        $hostAddrLine2 = $homeMeal->getHost()->getProfile()->getAddressLine2();
        $hostAddrPLZ = $homeMeal->getHost()->getProfile()->getAreaCode();
        $hostAddrCity = $homeMeal->getHost()->getProfile()->getCity();
        $locastionString = implode(' ', array($hostAddrLine1, $hostAddrLine2, $hostAddrPLZ, $hostAddrCity));

        $hostProfileAddress->setLocationString($locastionString);

        $homeMeal->setMealAddresses(new ArrayCollection(array($hostProfileAddress)));
        // Adding the default category
        // @todo: there must be a better way to get the "default" category.
        $defaultCategory = $this->entityManager->getRepository('ApiBundle:Meal\BaseMealCategory')->findAll()[0];
        $categories = new ArrayCollection(array($defaultCategory));
        $homeMeal->setCategories($categories);

        return $this->createFromEntity($homeMeal);
    }

    /**
     * Restore the HomeMeal specified by ID and wraps it into a HomeMealServiceData result and returns that.
     *
     * @param int $id the ID of the HomeMeal to restore
     *
     * @return HomeMealServiceData contains the result of the restore operation
     */
    public function restore(int $id): HomeMealServiceData
    {
        /** @var HomeMeal $homeMeal */
        $homeMeal = $this->getEntityManager()
            ->getRepository($this->getEntityName())
            ->find($id);

        if (null === $homeMeal) {
            $this->dataModel->addError('HomeMeal $id does not exist!');
            $this->dataModel->setValidity(false);

            return $this->dataModel;
        }

        $this->dataModel->setHomeMeal($homeMeal);

        return $this->dataModel;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param int $id
     *
     * @return HomeMeal
     */
    public function restoreHomeMeal(int $id): HomeMeal
    {
        $homeMeal = $this->getEntityManager()
            ->getRepository($this->getEntityName())
            ->find($id);

        return $homeMeal;
    }

    public function getTree(HomeMeal $homeMeal): array
    {
        $this->logger->addDebug(sprintf('->%s', __METHOD__));

        return $this->getEntityManager()
            ->getRepository($this->getEntityName())
            ->getFlatTree('/'.$homeMeal->getId());
    }

    public function createJoinRequest(HomeMeal $meal, MMUser $user)
    {
        $meal->getJoinRequests();
    }

    public function findByTitle($mealTitle)
    {
        return $this->entityManager->getRepository('ApiBundle:Meal\HomeMeal')->findBy(
            array(
                'title' => $mealTitle,
                'leaf' => 1,
            )
        );
    }

    public function findAllBy(array $criteria)
    {
        return $this->entityManager->getRepository('ApiBundle:Meal\HomeMeal')->findBy(
            $criteria
        );
    }

    public function findRunningByToday()
    {
        throw new \BadMethodCallException('This has not been implemented yet!');
    }

    public function findRunningByCategory($category)
    {
    }
}
