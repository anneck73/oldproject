<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Model;

use Mealmatch\ApiBundle\Entity\LegalFile;
use Mealmatch\ApiBundle\Exceptions\ServiceDataException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class LegalFileServiceData does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class LegalFileServiceData extends AbstractServiceDataManager
{
    const ENITY_KEY = 'LegalFile';

    public function __construct(LegalFile $legalFile)
    {
        parent::__construct('LegalFile', $legalFile);
    }

    public function getLegalFileEntity(): LegalFile
    {
        $entity = parent::getEntity(self::ENITY_KEY);
        if ($entity instanceof LegalFile) {
            return $entity;
        }
        throw new ServiceDataException('Expected LegalFile!!!');
    }

    public function getFile(): UploadedFile
    {
        $this->getLegalFileEntity()->getFileData();
    }
}
