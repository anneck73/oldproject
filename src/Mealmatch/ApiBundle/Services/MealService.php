<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Entity\Meal\BaseMeal;
use Mealmatch\ApiBundle\Entity\Meal\HomeMeal;
use Mealmatch\ApiBundle\Entity\Meal\MealEvent;
use Mealmatch\ApiBundle\Entity\Meal\MealJoinRequest;
use Mealmatch\ApiBundle\Entity\Meal\MealOffer;
use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Mealmatch\ApiBundle\MealMatch\CollectionHelper;
use Mealmatch\ApiBundle\Model\MealServiceData;
use Mealmatch\ApiBundle\Repository\Meal\BaseMealRepository;
use MMUserBundle\Entity\MMUser;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

/**
 * The MealService class uses ProMealService, HomeMealService, MealEventService ... and more when they are
 * needed together to provide functionality.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class MealService
{
    public const HOME_SPEC = 'HomeMeal';
    public const SOCIAL_SPEC = 'SocialMeal';
    public const BUSINESS_SPEC = 'ProMeal';

    /**
     * @todo: Finish PHPDoc!
     *
     * @var Logger
     */
    private $logger;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var Translator
     */
    private $translator;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var ProMealService
     */
    private $proMealService;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var HomeMealService
     */
    private $homeMealService;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var MealEventService
     */
    private $mealEventServcie;

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
    }

    public function addProMealService(ProMealService $proMealService)
    {
        $this->proMealService = $proMealService;
    }

    public function addHomeMealService(HomeMealService $homeMealService)
    {
        $this->homeMealService = $homeMealService;
    }

    public function addMealEventService(MealEventService $mealEventService)
    {
        $this->mealEventServcie = $mealEventService;
    }

    /**
     * Queries all current Meals and returns a collection of BaseMeals who are outdated.
     *
     * It queries all leaf BaseMeal with status === ApiConstants::MEAL_STATUS_RUNNING,
     *
     *
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return Collection of BaseMeals to be transitioned to finished
     */
    public function findFinishedMeals(): Collection
    {
        /** @var BaseMealRepository $baseMealRepo */
        $baseMealRepo = $this->entityManager->getRepository('ApiBundle:Meal\BaseMeal');

        // The query is done using the entity repository ...
        return $baseMealRepo->findOutdated();
    }

    public function findAll(): array
    {
        $allHomeMeals = $this->homeMealService->findAll();
        $allProMeals = $this->proMealService->findAll();

        return array_merge($allHomeMeals, $allProMeals);
    }

    public function findAllBy(array $criteria = array()): array
    {
        $criteria = array_merge($criteria, array('leaf' => true));
        $allHomeMeals = $this->homeMealService->findAllBy($criteria);
        $allProMeals = $this->proMealService->findAllBy($criteria);

        return array_merge($allHomeMeals, $allProMeals);
    }

    public function findRunningByCategory(string $category)
    {
        $allHomeMeals = $this->homeMealService->findRunningByCategory($category);
        $allProMeals = $this->proMealService->findRunningByCategory($category);

        return array_merge($allHomeMeals, $allProMeals);
    }

    public function isGuest(BaseMeal $baseMeal, MMUser $user = null)
    {
        if (null === $user) {
            return false;
        }

        return $baseMeal->getGuests()->contains($user);
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param MealJoinRequest $joinRequest
     * @param MMUser          $user
     * @param string          $myArgument  with a *description* of this argument, these may also
     *                                     span multiple lines
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Mealmatch\ApiBundle\Exceptions\ServiceDataException
     *
     * @return MealServiceData
     */
    public function joinMeal(MealJoinRequest $joinRequest, MMUser $user): MealServiceData
    {
        $baseMeal = $joinRequest->getBaseMeal();
        $data = new MealServiceData('BaseMeal', $baseMeal);

        $data->addEntity('MealJoinRequest', $joinRequest);

        if ($baseMeal->getGuests()->count() === $baseMeal->getMaxNumberOfGuest()) {
            $data->addError('Das Meal ist bereits ausgebucht!');

            return $data;
        }

        $baseMeal->addGuest($user);
        $this->entityManager->persist($baseMeal);

        $joinRequest->setStatus('JOINED_FREE');
        $this->entityManager->persist($joinRequest);

        $this->entityManager->flush();

        $data->setData('BaseMeal', $baseMeal);

        return $data;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param ProMeal $proMeal
     * @param string  $status
     * @param string  $myArgument with a *description* of this argument, these may also
     *                            span multiple lines
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return MealServiceData
     */
    public function createAllProMealEvents(
        ProMeal $proMeal,
        string $status = ApiConstants::MEAL_STATUS_CREATED
    ): MealServiceData {
        $data = new MealServiceData('ProMeal', $proMeal);

        // (1) get all event dates ...
        $allProMealDates = $this->mealEventServcie->getAvailableDatesForProMeal($proMeal);

        // (2) process all event dates and create new ProMeals,
        //     one for each event date.
        $createdMeals = $this->createChildsOfProMeal($proMeal, $status, $allProMealDates);

        // (3) Update this root-ProMeal status
        $proMeal->setStatus($status);
        $this->entityManager->persist($proMeal);
        $this->entityManager->flush();

        // (4) put the created ProMeals into the result data
        //     and this updated root-ProMeal too.
        $data->setData('createdMeals', $createdMeals->toArray());
        $data->setProMeal($proMeal);

        // no validation, but since default is false, we set it to true, to indicate that we have processed something.
        $data->setValidity(true);

        return $data;
    }

    public function createAllHomeMealEvents(
        HomeMeal $homeMeal,
        string $status = ApiConstants::MEAL_STATUS_CREATED
    ): MealServiceData {
        $data = new MealServiceData('HomeMeal', $homeMeal);

        // (1) get all event dates ...
        $allProMealDates = $this->mealEventServcie->getAvailableDatesForHomeMeal($homeMeal);

        // (2) process all event dates and create new ProMeals,
        //     one for each event date.
        $createdMeals = $this->createChildsOfHomeMeal($homeMeal, $status, $allProMealDates);

        // (3) Update this root-ProMeal status
        $homeMeal->setStatus($status);
        $this->entityManager->persist($homeMeal);
        $this->entityManager->flush();

        // (4) put the created ProMeals into the result data
        //     and this updated root-ProMeal too.
        $data->setData('createdMeals', $createdMeals->toArray());
        $data->setHomeMeal($homeMeal);

        // no validation, but since default is false, we set it to true, to indicate that we have processed something.
        $data->setValidity(true);

        return $data;
    }

    /**
     * Will remove a MealOffer from a ProMeal.
     *
     * @param int $id      the ID of the ProMeal
     * @param int $offerID the ID of the MealOffer
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function removeOfferFromProMeal(int $id, int $offerID)
    {
        $proMeal = $this->entityManager->find('ApiBundle:Meal\ProMeal', $id);
        $mealOffer = $this->entityManager->find('ApiBundle:Meal\MealOffer', $offerID);

        if (null === $proMeal) {
            return;
        }
        if (null === $mealOffer) {
            return;
        }

        $proMeal->removeMealOffer($mealOffer);
        $this->entityManager->remove($mealOffer);
        $this->entityManager->persist($proMeal);
        $this->entityManager->flush();
    }

    /**
     * Will remove a MealEvent from a Meal.
     *
     * @param int $id      the ID of the Meal
     * @param int $eventID the ID of the MealEvent
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function removeEventFromMeal(int $id, int $eventID)
    {
        $meal = $this->entityManager->find('ApiBundle:Meal\BaseMeal', $id);
        $mealEvent = $this->entityManager->find('ApiBundle:Meal\MealEvent', $eventID);
        if (null === $meal || null === $mealEvent) {
            $this->logger->err('Null value?! ');

            return;
        }
        $meal->removeMealEvent($mealEvent);
        $this->entityManager->persist($meal);
        $this->entityManager->remove($mealEvent);
        $this->entityManager->flush();
    }

    /**
     * Returns 'ProMeal' or 'HomeMeal' depending on MealType.
     *
     * @param int $id the ID of the Meal
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return string the MealType as a string
     */
    public function getMealType(int $id): string
    {
        $meal = $this->entityManager->find('ApiBundle:Meal\BaseMeal', $id);
        if ($meal instanceof ProMeal) {
            return 'ProMeal';
        }

        return 'HomeMeal';
    }

    /**
     * Persist and flush BaseMeal.
     *
     * @param BaseMeal $baseMeal the BaseMeal to persist and flush
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return BaseMeal the freshly restored BaseMeal
     */
    public function safe(BaseMeal $baseMeal): BaseMeal
    {
        $this->entityManager->persist($baseMeal);
        $this->entityManager->flush();

        return $this->restore($baseMeal->getId());
    }

    public function restore(int $id): BaseMeal
    {
        return $this->entityManager->find('ApiBundle:Meal\BaseMeal', $id);
    }

    /**
     * Returns the HomeMealService.
     *
     * @return HomeMealService
     */
    public function getHomeMealService(): HomeMealService
    {
        return $this->homeMealService;
    }

    /**
     * Returns all currently running meals of the specified $userAccount.
     *
     * @param MMUser $userAccount the host of the meals
     *
     * @return Collection of Meals
     */
    public function getRunningByUser(MMUser $userAccount): Collection
    {
        // All "leaf's" running by user ...
        $baseMeals = $this->entityManager->getRepository('ApiBundle:Meal\BaseMeal')->findBy(
            array(
                'host' => $userAccount,
                'leaf' => 1,
                'status' => ApiConstants::MEAL_STATUS_RUNNING,
            )
        );

        $baseMealC = new ArrayCollection($baseMeals);

        return CollectionHelper::sortByStartDate($baseMealC);
    }

    public function getJoinedByUser(MMUser $userAccount)
    {
        $this->logger->addDebug('get all meals where user '.$userAccount->getId().' is guest!');
        /** @var BaseMealRepository $baseMealRepo */
        $baseMealRepo = $this->entityManager->getRepository('ApiBundle:Meal\BaseMeal');
        $joinedMeals = $baseMealRepo->findJoinedMealsByUserAccount($userAccount);
        $this->logger->addDebug('Found '.$joinedMeals->count().' where user '.$userAccount->getId().' is guest!');

        return $joinedMeals;
    }

    /**
     * @todo: ALWAYS RETURN FALSE!!! FIX THAT !
     * Returns true if the specified $userAccount is a guest in a meal of the $hostAccount.
     *
     * @param MMUser $userAccount the userAccount to check for guest status of host meals
     * @param MMUser $hostAccount the hostAccount to get all meals from
     *
     * @return bool true if the user is a guest in a meal hosted by the host account, false if not
     */
    public function isUserGuestOfHost(MMUser $userAccount, MMUser $hostAccount): bool
    {
        $hostingMeals = $hostAccount->getHostingMeals();
        /** @var BaseMeal $meal */
        foreach ($hostingMeals as $meal) {
            if ($meal->isGuest($userAccount)) {
                return true;
            }
        }

        return false;
    }

    public function getAllCategories(): Collection
    {
        return new ArrayCollection(
            $this->entityManager->getRepository('ApiBundle:Meal\BaseMealCategory')->findAll()
        );
    }

    /**
     * For each eventDate a new ProMeal is created as a child of the specified ProMeal.
     * The created childs are returned.
     *
     * @param ProMeal         $proMeal
     * @param ArrayCollection $eventDates
     *
     * @return ArrayCollection
     */
    private function createChildsOfProMeal(
        ProMeal $proMeal,
        string $status,
        ArrayCollection $eventDates
    ): ArrayCollection {
        // To track what we do ...
        $createdMeals = new ArrayCollection();

        // Process one new ProMeal for each MealEvent e.g. the "Date".
        foreach ($eventDates as $date) {
            // The new ProMeal (to be Child of)
            $newProMeal = new ProMeal();
            $newProMeal->setHost($proMeal->getHost());
            $newProMeal->setTitle($proMeal->getTitle());
            $newProMeal->setDescription($proMeal->getDescription());
            $newProMeal->setMealAddresses($proMeal->getMealAddresses());
            $newProMeal->setCategories($proMeal->getCategories());
            // Copy MealOffers ...
            /** @var MealOffer $offer */
            foreach ($proMeal->getMealOffers() as $offer) {
                $newOffer = clone $offer;
                $newOffer->rebuildHash();
                $this->entityManager->persist($newOffer);
                $newProMeal->addMealOffer($newOffer);
            }
            $newProMeal->setMealOfferNotes($proMeal->getMealOfferNotes());
            $newProMeal->setMaxNumberOfGuest($proMeal->getMaxNumberOfGuest());
            $newProMeal->setSpecials($proMeal->getSpecials());
            $newProMeal->setTableTopic($proMeal->getTableTopic());
            // Set one mealEvent.
            $mealEvents = new ArrayCollection();
            $mealEvent = (new MealEvent())->setStartDateTime($date['start'])->setEndDateTime($date['end']);
            $mealEvents->add($mealEvent);
            $newProMeal->setMealEvents($mealEvents);
            // Set the status as specified
            $newProMeal->setStatus($status);
            // Persist it ...
            $this->entityManager->persist($newProMeal);
            // The track it ...
            $createdMeals->add($newProMeal);
        }

        // Write everything into DB.
        $this->entityManager->flush();

        // Now for every ProMeal we created, we set it to be a "Child-Node-Of" the ProMeal used to create it.
        /** @var ProMeal $child */
        foreach ($createdMeals as $child) {
            $child->setChildNodeOf($proMeal);
            $child->setLeaf(true);
            $this->entityManager->persist($child);
        }
        // Just to be sure, root it not leaf.
        $proMeal->setLeaf(false);

        // Persist and write to DB.
        $this->entityManager->persist($proMeal);
        $this->entityManager->flush();

        // Return our result.
        return $createdMeals;
    }

    private function createChildsOfHomeMeal(
        HomeMeal $homeMeal,
        string $status,
        ArrayCollection $eventDates
    ): ArrayCollection {
        $createdMeals = new ArrayCollection();
        foreach ($eventDates as $date) {
            $newHomeMeal = new HomeMeal();

            $newHomeMeal->setHost($homeMeal->getHost());
            $newHomeMeal->setTitle($homeMeal->getTitle());
            $newHomeMeal->setMealAddresses($homeMeal->getMealAddresses());
            $newHomeMeal->setCategories($homeMeal->getCategories());
            $newHomeMeal->setMealParts($homeMeal->getMealParts());
            $newHomeMeal->setMealMain($homeMeal->getMealMain());
            $newHomeMeal->setMealDesert($homeMeal->getMealDesert());
            $newHomeMeal->setMealStarter($homeMeal->getMealStarter());
            $newHomeMeal->setDescription($homeMeal->getDescription());
            $newHomeMeal->setSharedCost($homeMeal->getSharedCost());
            $newHomeMeal->setSharedCostCurrency($homeMeal->getSharedCostCurrency());
            $newHomeMeal->setMaxNumberOfGuest($homeMeal->getMaxNumberOfGuest());

            $mealEvents = new ArrayCollection();
            $mealEvent = (new MealEvent())->setStartDateTime($date['start'])->setEndDateTime($date['end']);
            $mealEvents->add($mealEvent);
            $newHomeMeal->setMealEvents($mealEvents);

            $newHomeMeal->setStatus($status);

            $this->entityManager->persist($newHomeMeal);
            $createdMeals->add($newHomeMeal);
        }
        $this->entityManager->flush();

        /** @var ProMeal $child */
        foreach ($createdMeals as $child) {
            $child->setChildNodeOf($homeMeal);
            $child->setLeaf(true);
            $this->entityManager->persist($child);
        }
        $homeMeal->setLeaf(false);
        $this->entityManager->persist($homeMeal);
        $this->entityManager->flush();

        return $createdMeals;
    }
}
