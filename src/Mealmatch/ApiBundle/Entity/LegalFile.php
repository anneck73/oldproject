<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Mealmatch\ApiBundle\MealMatch\Traits\Hashable;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A LegalFile represents a file of mimeType application/pdf, identified by its "type" like "tos", "licence", "etc".
 *
 * @ORM\Entity()
 */
class LegalFile extends UploadableFile
{
    /*
     * Traits
     */
    use ORMBehaviors\Blameable\Blameable;
    use
        ORMBehaviors\Timestampable\Timestampable;
    use
        Hashable;

    /**
     * The "type" like "TOS, License, Useragreement, etc".
     *
     * @var string
     * @ORM\Column(name="legal_type", type="string")
     */
    protected $legalType = 'undefined';

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Assert\File(mimeTypes={
     *     "application/pdf"
     * })
     *
     * @var File
     */
    protected $fileData;

    /**
     * Create a new LegalFile entity and initializes the hash value!
     * LegalFile constructor.
     */
    public function __construct()
    {
        parent::__construct();
        // Make sure hash is not null ...
        $this->initHash();
    }

    /**
     * @return string
     */
    public function getLegalType(): string
    {
        return $this->legalType;
    }

    /**
     * @param string $legalType
     *
     * @return LegalFile
     */
    public function setLegalType(string $legalType): self
    {
        $this->legalType = $legalType;

        return $this;
    }
}
