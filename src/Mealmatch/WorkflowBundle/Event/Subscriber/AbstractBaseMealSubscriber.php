<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\WorkflowBundle\Event\Subscriber;

use Doctrine\ORM\EntityManager;
use FOS\MessageBundle\Composer\Composer;
use FOS\MessageBundle\Sender\Sender;
use Mealmatch\ApiBundle\Services\MealService;
use Monolog\Logger;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * The AbstractBaseMealSubscriber implements common services to all extending classes.
 *
 * This abstraction gives access to MealService, Logger, Composer, TokenStorage, Sender, EntityManager.
 */
class AbstractBaseMealSubscriber
{
    /**
     * Used to create meals as specified.
     *
     * @var MealService
     */
    protected $mealService;

    /**
     * The logger.
     *
     * @var Logger
     */
    protected $logger;

    /**
     * FOS:Message composer service.
     *
     * @var Composer
     */
    protected $composer;

    /**
     * The TokenStorage to obtain the current user.
     *
     * @var TokenStorage
     */
    protected $storage;

    /**
     * The FOS:Message sender service.
     *
     * @var Sender
     */
    protected $sender;

    /**
     * The entiy manager to get user details.
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * BaseMealCreateAllTransition constructor.
     *
     * @param Logger       $logger
     * @param MealService  $mealService
     * @param Composer     $composer
     * @param Sender       $sender
     * @param TokenStorage $storage
     */
    public function __construct(
        Logger $logger,
        EntityManager $entityManager,
        MealService $mealService,
        Composer $composer,
        Sender $sender,
        TokenStorage $storage
    ) {
        $this->mealService = $mealService;
        $this->logger = $logger;
        $this->composer = $composer;
        $this->storage = $storage;
        $this->sender = $sender;
        $this->entityManager = $entityManager;
    }
}
