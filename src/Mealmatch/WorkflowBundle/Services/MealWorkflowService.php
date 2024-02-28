<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\WorkflowBundle\Services;

use Doctrine\ORM\EntityManager;
use Mealmatch\ApiBundle\Entity\Meal\BaseMeal;
use Monolog\Logger;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\Workflow;

class MealWorkflowService
{
    /**
     * The logger used.
     *
     * @var Logger
     */
    private $logger;

    /**
     * The entity manager used.
     *
     * @var EntityManager
     */
    private $entityManager;

    /**
     * The translator used.
     *
     * @var Translator
     */
    private $translator;

    /**
     * The workflow to use.
     *
     * @var Workflow
     */
    private $workflow;

    /**
     * MealWorkflowService constructor.
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

    public function addWorkflowService(Workflow $workflow)
    {
        $this->workflow = $workflow;
    }

    public function doTransition(BaseMeal $baseMeal, string $trans)
    {
        try {
            $this->workflow->apply($baseMeal, $trans);
        } catch (LogicException $logicException) {
            // ...
        }
    }
}
