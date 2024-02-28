<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Services\Enterprise;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Mealmatch\ApiBundle\Entity\EntityData;
use Mealmatch\ApiBundle\Exceptions\ServiceDataException;
use Psr\Log\LoggerInterface as Logger;
use Symfony\Component\Translation\Translator;

class AbstractEnterpriseService
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

    /**
     * Will execute persist and flush on provided $entityData and publish all errors.
     *
     * @param EntityData $entityData
     *
     * @throws ServiceDataException
     */
    protected function persistAndFlushData(EntityData $entityData): void
    {
        // error msg prefix
        $_prefix = 'persistAndFlushData ERROR on: '.$entityData->getFQDN()
        .' with msg: ';
        try {
            $this->entityManager->persist($entityData);
            $this->entityManager->flush();
        } catch (ORMInvalidArgumentException $ormExc) {
            $this->logger->addError($_prefix.$ormExc->getMessage());
            throw new ServiceDataException($ormExc->getMessage());
        } catch (OptimisticLockException $lockException) {
            $this->logger->addError($_prefix.$lockException->getMessage());
            throw new ServiceDataException($lockException->getMessage());
        } catch (ORMException $ORMException) {
            $this->logger->addError($_prefix.$ORMException->getMessage());
            throw new ServiceDataException($ORMException->getMessage());
        }
    }
}
