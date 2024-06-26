<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Entity\UploadableFile;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * A RestaurantImage represents an image used by the MMUser/MMRestaurantProfile.
 *
 * @ORM\Entity()
 * @Vich\Uploadable()
 */
class RestaurantFile extends UploadableFile
{
    /**
     * Links to the restaurant profile, owner of this image.
     *
     * @var MMRestaurantProfile
     *
     * Many Features have One Product
     * @ManyToOne(targetEntity="MMUserBundle\Entity\MMRestaurantProfile", inversedBy="legalFiles")
     * @JoinColumn(name="restaurant_profile_id", referencedColumnName="id")
     */
    protected $restaurantProfile;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var string
     * @ORM\Column(name="legal_type", type="string", nullable=false)
     */
    protected $legalType = ApiConstants::LEGAL_FILE_TYPE_BUSINESS_REGISTRATION;

    /**
     * NOTE: this property is not part of the entity data.
     *
     * @var File
     * @Vich\UploadableField(
     *     mapping="restaurant_file",
     *     fileNameProperty="fileName",
     *     size="fileSize",
     *     mimeType="mimeType")
     */
    protected $fileData;

    public function __construct()
    {
        $this->initHash();
    }

    public function __toString()
    {
        return __CLASS__.$this->getId();
    }

    /**
     * Set the filename.
     *
     * @param string $name the filename to be used
     *
     * @return UploadableFile
     */
    public function setFileName($name): UploadableFile
    {
        return parent::setFileName($name);
    }

    /**
     * Returns the restaurant profile of this image.
     *
     * @return MMRestaurantProfile
     */
    public function getRestaurantProfile(): MMRestaurantProfile
    {
        return $this->restaurantProfile;
    }

    /**
     * Set the restaurant profile of this image.
     *
     * @param MMRestaurantProfile $restaurantProfile
     *
     * @return RestaurantFile the RestaurantFile
     */
    public function setRestaurantProfile(MMRestaurantProfile $restaurantProfile)
    {
        $this->restaurantProfile = $restaurantProfile;

        return $this;
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
     * @return RestaurantFile
     */
    public function setLegalType(string $legalType): self
    {
        $this->legalType = $legalType;

        return $this;
    }
}
