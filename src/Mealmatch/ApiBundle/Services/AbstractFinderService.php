<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Mealmatch\ApiBundle\ApiConstants;
use MMUserBundle\Entity\MMUser;

/**
 * The AbstractFinderService groups search utility methods for the "current" Entity in the implementing
 * service class. (this->getEntityName).
 *
 * All finder methods search only for leaf-Meals by default. If you need to find a root-Meal set the 2nd parameter to 0.
 */
abstract class AbstractFinderService implements FinderServiceInterface
{
    /**
     * Finds all Meals associated to the specified user as the owner of the meal.
     *
     * @param MMUser $MMUser   the host user to determine all meals
     * @param int    $leaf     Search for leaf or root meal
     * @param mixed  $criteria
     *
     * @return array of meal entites
     */
    public function findAllByOwner(MMUser $MMUser, $leaf = 1, $criteria = array()): array
    {
        $defaults = array(
            'host' => $MMUser->getId(),
            'leaf' => $leaf,
        );
        $criteria = array_merge($criteria, $defaults);

        return $this->getEntityManager()->getRepository($this->getEntityName())->findBy(
          $criteria
        );
    }

    /**
     * Finds "finished" Meals associated to the specified user as the owner of the meal.
     *
     * @param MMUser $MMUser the host user to determine all meals
     * @param int    $leaf   Search for leaf or root meal
     *
     * @return array of meal entites
     */
    public function findFinishedByOwner(MMUser $user, $leaf = 1): array
    {
        return $this->getEntityManager()->getRepository($this->getEntityName())->findBy(
            array(
                'host' => $user->getId(),
                'status' => ApiConstants::MEAL_STATUS_FINISHED,
                'leaf' => $leaf,
            )
        );
    }

    /**
     * Finds "running" Meals associated to the specified user as the owner of the meal.
     *
     * @param MMUser $MMUser the host user to determine all meals
     * @param int    $leaf   Search for leaf or root meal
     *
     * @return array of meal entites
     */
    public function findRunningByOwner(MMUser $user, $leaf = 1): array
    {
        return $this->getEntityManager()->getRepository($this->getEntityName())->findBy(
            array(
                'host' => $user->getId(),
                'status' => ApiConstants::MEAL_STATUS_RUNNING,
                'leaf' => $leaf,
            )
        );
    }

    /**
     * Finds "stopped" Meals associated to the specified user as a Host of the meal.
     *
     * @param MMUser $MMUser the host user to determine all meals
     * @param int    $leaf   Search for leaf or root meal
     *
     * @return array of meal entites
     */
    public function findStoppedByOwner(MMUser $user, $leaf = 1): array
    {
        return $this->getEntityManager()->getRepository($this->getEntityName())->findBy(
            array(
                'host' => $user->getId(),
                'status' => ApiConstants::MEAL_STATUS_STOPPED,
                'leaf' => $leaf,
            )
        );
    }

    /**
     * Finds "created" Meals associated to the specified user as a Host of the meal.
     *
     * @param MMUser $MMUser the host user to determine all meals
     * @param int    $leaf   Search for leaf or root meal
     *
     * @return ArrayCollection of meal entites
     */
    public function findCreatedByOwnerAsCollection(MMUser $user, $leaf = 1): ArrayCollection
    {
        return new ArrayCollection($this->findCreatedByOwner($user, $leaf));
    }

    /**
     * Finds "created" Meals associated to the specified user as a Host of the meal.
     *
     * @param MMUser $MMUser the host user to determine all meals
     * @param int    $leaf   Search for leaf or root meal
     *
     * @return array of meal entites
     */
    public function findCreatedByOwner(MMUser $user, $leaf = 1): array
    {
        return $this->getEntityManager()->getRepository($this->getEntityName())->findBy(
            array(
                'host' => $user->getId(),
                'status' => ApiConstants::MEAL_STATUS_CREATED,
                'leaf' => $leaf,
            )
        );
    }

    /**
     * Finds "ready" Meals associated to the specified user as a Host of the meal.
     *
     * @param MMUser $MMUser the host user to determine all meals
     * @param int    $leaf   Search for leaf or root meal
     *
     * @return array of meal entites
     */
    public function findReadyByOwner(MMUser $user, $leaf = 1): array
    {
        return $this->getEntityManager()->getRepository($this->getEntityName())->findBy(
            array(
                'host' => $user->getId(),
                'status' => ApiConstants::MEAL_STATUS_READY,
                'leaf' => 1,
            )
        );
    }

    /**
     * Finds all Meals.
     *
     * @param int $leaf Search for leaf or root meal
     *
     * @return array of meal entites
     */
    public function findAll($leaf = 1): array
    {
        return $this->getEntityManager()->getRepository($this->getEntityName())->findBy(
            array('leaf' => $leaf)
        );
    }

    public function findAllRunning($leaf = 1): array
    {
        return $this->getEntityManager()->getRepository($this->getEntityName())->findBy(
            array('leaf' => $leaf, 'status' => ApiConstants::MEAL_STATUS_RUNNING)
        );
    }

    /**
     * Returns true if a BaseMeal exists for the specified ID.
     *
     * @param int $id the ID of the BaseMeal entity
     *
     * @return bool true if exists, otherwise false
     */
    public function exists(int $id): bool
    {
        $found = $this->getEntityManager()->getRepository($this->getEntityName())->find($id);

        return (null === $found) ? false : true;
    }
}
