<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMApiBundle;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use MMApiBundle\Entity\Meal;
use MMApiBundle\Exceptions\MealCreateFailed;
use MMUserBundle\Entity\MMUser;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @todo: Finish PHPDoc!
 * The MMMeal is a symfony service for handling meals.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class MMMeal
{
    const DEFAULT_SPEC = 'DEFAULT';
    const PRIVATE_SPEC = 'PRIVATE';
    const SOCIAL_SPEC = 'SOCIAL';
    const PRO_SPEC = 'PRO';

    /**
     * Allowed specifications for "kind of" meals.
     *
     * @var array
     */
    private static $specs = array(self::DEFAULT_SPEC, self::PRIVATE_SPEC, self::SOCIAL_SPEC, self::PRO_SPEC);

    /**
     * This service uses the logger.
     *
     * @var Logger
     */
    private $logger;
    /**
     * This service uses the entity manager.
     *
     * @var EntityManager
     */
    private $em;

    /**
     * The meal data.
     *
     * @var ArrayCollection
     */
    private $mealData;

    /**
     * Creates the mm.meal service.
     *
     * The meal service covers all business functionality for meals.
     *
     * @param Logger        $pLogger
     * @param EntityManager $pEntityManager
     */
    public function __construct(Logger $pLogger, EntityManager $pEntityManager)
    {
        $this->logger = $pLogger;
        $this->em = $pEntityManager;
        $this->mealData = new ArrayCollection();
        $this->logger->addDebug('Created Service: '.__CLASS__);
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param UserInterface $pUser
     * @param DateTime      $pStartDateTime
     * @param string        $pMealSpec
     *
     * @throws MealCreateFailed If something goes wrong!
     *
     * @return Collection containing a new meal of the specified kind
     *
     * @internal param string $mealSpecification
     */
    public function createBySpecification(
        UserInterface $pUser,
        DateTime $pStartDateTime,
        string $pLocationAddress,
        string $pMealSpec = self::DEFAULT_SPEC
    ): Collection {
        $this->setSpecification($pMealSpec);

        if ($pUser instanceof MMUser) {
            $this->createNew($pUser, $pStartDateTime, $pLocationAddress);
        } else {
            throw new MealCreateFailed('The specified user does not implement MMUser?!?! WTF!!!!');
        }

        return $this->mealData;
    }

    public function setStatus(Meal $pMeal, string $pStatus)
    {
        return $pMeal->setStatus($pStatus);
    }

    public function getEntity()
    {
        return $this->mealData->get('entitiy');
    }

    private function setSpecification(string $pSpec)
    {
        if (!\in_array($pSpec, self::$specs, true)) {
            throw new MealCreateFailed('Specification '.$pSpec.' undefined!');
        }

        $this->mealData->set('specification', $pSpec);
    }

    private function createNew(MMUser $pHost, DateTime $pStartDateTime, string $pLocationAddress)
    {
        // Create the meal entity ...
        $meal = new Meal();

        // Set the Host of the meal ...
        $meal->setHost($pHost);

        /**
         * The start of the meal needs to be at least +3 Hours into the future.
         */
        $now = new DateTime('now');
        $minStartDateTime = $now->modify('+3 Hours');
        if ($pStartDateTime < $minStartDateTime) {
            // the StartDateTime was not far enought into the future, we just set it to the minimum value ;)
            $meal->setStartDateTime($minStartDateTime);
        } else {
            $meal->setStartDateTime($pStartDateTime);
        }

        $meal->setStatus(Meal::$STATUS_RUNNING);

        $this->mealData->set('entitiy', new Meal());
    }
}
