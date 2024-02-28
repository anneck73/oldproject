<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Services;

use Doctrine\ORM\EntityManager;
use Mealmatch\ApiBundle\Entity\LegalFile;
use Mealmatch\ApiBundle\Exceptions\ServiceDataValidationException;
use Mealmatch\ApiBundle\Model\LegalFileServiceData;
use Monolog\Logger;

class LegalFileService
{
    /** @var Logger $logger */
    private $logger;
    /** @var EntityManager $em */
    private $em;
    /** @var AwsUploaderService $uploader */
    private $uploader;

    /**
     * LegalFileService constructor.
     *
     * @param Logger        $logger
     * @param EntityManager $entityManager
     */
    public function __construct(Logger $logger, EntityManager $entityManager, AwsUploaderService $awsUploader)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
        $this->uploader = $awsUploader;
    }

    /**
     * Creates a new LegalFile using raw file data and file meta data from the entity data.
     *
     * @param LegalFile $legalFile the LegalFile to create
     *
     * @return LegalFileServiceData legalFile data model
     */
    public function createByEntity(LegalFile $legalFile): LegalFileServiceData
    {
        $legalFileSD = new LegalFileServiceData($legalFile);
        if (!$legalFileSD->isValid()) {
            throw new ServiceDataValidationException('LegalFile ist not valid!');
        }
        // Upload and put the result into service data.
        $legalFileSD = $this->uploader->uploadByLegalFile($legalFileSD);

        // Use service data to retrieve values changed from uploading...
        $updatedFilename = $legalFileSD->getLegalFileEntity()->getFileName();
        $legalFile->setFileName($updatedFilename);

        $uploadedFile = $legalFileSD->getLegalFileEntity()->getFileData();
        $updatedMimeType = $uploadedFile->getMimeType();
        $legalFile->setMimeType($updatedMimeType);

        // Persist entity ...
        $this->em->persist($legalFile);

        // Update service data with persisted entity
        $legalFileSD->setData(LegalFileServiceData::ENITY_KEY, $legalFile);

        return $legalFileSD;
    }
}
