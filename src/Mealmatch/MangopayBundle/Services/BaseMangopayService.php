<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\MangopayBundle\Services;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface as Logger;
use Symfony\Component\Translation\Translator;

/**
 * Class BaseMangopayService.
 */
abstract class BaseMangopayService
{
    /**
     * The logger used.
     *
     * @var Logger
     */
    protected $logger;

    /**
     * The entity manager.
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Translations.
     *
     * @var Translator
     */
    protected $translator;

    /**
     * BaseCouponService constructor.
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
}
