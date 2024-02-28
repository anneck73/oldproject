<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Entity\Meal;

use Doctrine\ORM\Mapping as ORM;
use Mealmatch\ApiBundle\Entity\AbstractEntity;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * The Categories for BaseMeals.
 *
 *
 * @ORM\Entity(repositoryClass="Mealmatch\ApiBundle\Repository\Meal\BaseMealCategoryRepository")
 * @Vich\Uploadable()
 */
class BaseMealCategory extends AbstractEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=190, unique=true)
     */
    private $name = '';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description = '';

    /**
     * @var string
     *
     * @ORM\Column(name="image_url", type="string", length=255, nullable=true)
     */
    private $imageURL = '';

    /**
     * @Vich\UploadableField(mapping="basemeal_category_image", fileNameProperty="imageURL")
     *
     * @var File
     */
    private $imageFile;

    /**
     * Bidirectional - Many BaseMealCategories are linked to many BaseMeals (INVERSE SIDE!!!).
     *
     * @ORM\ManyToMany(targetEntity="Mealmatch\ApiBundle\Entity\Meal\BaseMeal", mappedBy="categories")
     */
    private $baseMeal;

    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * @param int $id
     *
     * @return BaseMealCategory
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return BaseMealCategory
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return BaseMealCategory
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getImageURL()
    {
        return $this->imageURL;
    }

    /**
     * @param string $imageURL
     *
     * @return BaseMealCategory
     */
    public function setImageURL($imageURL): self
    {
        $this->imageURL = $imageURL;

        return $this;
    }

    /**
     * @return File|null
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * @param File $imageFile
     *
     * @return BaseMealCategory
     */
    public function setImageFile(File $imageFile): self
    {
        $this->imageFile = $imageFile;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBaseMeal()
    {
        return $this->baseMeal;
    }

    /**
     * @param BaseMeal $baseMeal
     *
     * @return BaseMealCategory
     */
    public function setBaseMeal($baseMeal)
    {
        $this->baseMeal = $baseMeal;

        return $this;
    }
}
