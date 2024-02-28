<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Entity\Meal;

use Doctrine\ORM\Mapping as ORM;
use Mealmatch\ApiBundle\Entity\UploadableFile;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * A MealImage represents an image used by ProMeal, HomeMeal, ...
 *
 * @ORM\Entity()
 * @Vich\Uploadable()
 */
class MealImage extends UploadableFile
{
    /**
     * NOTE: this property is not part of the entity data.
     *
     * @var File
     * @Vich\UploadableField(mapping="meal_image", fileNameProperty="fileName", size="fileSize", mimeType="mimeType")
     */
    protected $fileData;
}
