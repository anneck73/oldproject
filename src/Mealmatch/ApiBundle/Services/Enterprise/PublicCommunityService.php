<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Services\Enterprise;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Model\User;
use Mealmatch\ApiBundle\Entity\Community\Community;
use Mealmatch\ApiBundle\Exceptions\MealmatchException;
use Mealmatch\ApiBundle\Exceptions\ServiceDataException;
use Mealmatch\ApiBundle\Model\CommunityData;
use Psr\Log\LoggerInterface as Logger;
use Symfony\Component\Translation\Translator;

class PublicCommunityService extends AbstractEnterpriseService
{
    /**
     * PublicMangopayService constructor.
     *
     * @param Logger        $logger
     * @param EntityManager $entityManager
     * @param Translator    $translator
     */
    public function __construct(
        Logger $logger,
        EntityManager $entityManager,
        Translator $translator)
    {
        parent::__construct($logger, $entityManager, $translator);
    }

    /**
     * Creates a community-data-model with the given namen.
     *
     * @param string $name
     *
     * @return CommunityData
     */
    public function create($name = 'DEFAULT'): CommunityData
    {
        $newC = new Community();
        $newC->setName($name);
        $newC->setGroups(new ArrayCollection());
        $newC->setPermissions(new ArrayCollection());
        $newC->setPrivate(false);
        $newC->setProperties(new ArrayCollection());
        $createErrors = array();
        try {
            $this->persistAndFlushData($newC);
        } catch (ServiceDataException $serviceDataException) {
            $createErrors[] = $serviceDataException->getMessage();
        }
        // Create Model with Entity
        /** @var CommunityData $newCData */
        $newCData = new CommunityData($newC);
        // Try to extract JSON data representation.
        try {
            $jsonData = $newCData->getDataAsJson();
        } catch (MealmatchException $mealmatchException) {
            $createErrors[] = $mealmatchException->getMessage();
        }

        // Add Errors/Exception-Messages if there have been any...
        if (\count($createErrors) > 0) {
            $newCData->addErrorMessage('CREATE', json_encode($createErrors));
        }

        return $newCData;
    }

    public function registerNewMember(User $user): void
    {
    }
}
