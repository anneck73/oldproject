<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Super entity class for all files uploaded.
 *
 * @ORM\MappedSuperclass
 */
class UploadableFile extends AbstractEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="mimeType", type="string", length=255)
     */
    protected $mimeType = 'undefined';

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    protected $fileName = 'undefined';

    /**
     * @ORM\Column(type="integer", length=255)
     *
     * @var int
     */
    protected $fileSize = 0;

    /**
     * File metadata, basically anything that fits into a collection.
     *
     * @ORM\Column(name="file_meta", type="json_array")
     *
     * @var Collection
     */
    protected $fileMeta = array();

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @var File
     */
    protected $fileData;

    /**
     * The unique ID of the entity.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Returns the unique ID of the entity or NULL if the entity is not managed.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return UploadableFile
     */
    public function setFileName($name): self
    {
        $this->fileName = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    /**
     * @param int $fileSize
     *
     * @return UploadableFile
     */
    public function setFileSize($fileSize): self
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    /**
     * Get mimeType.
     *
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * Set mimeType.
     *
     * @param string $mimeType
     *
     * @return UploadableFile
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * @return File|null
     */
    public function getFileData()
    {
        return $this->fileData;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File $file
     *
     * @return $this
     */
    public function setFileData(File $fileData = null)
    {
        $this->fileData = $fileData;

        if ($fileData) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTime('now');
        }

        return $this;
    }

    /**
     * Return the file meta data as JSON.
     *
     * @return string jSON file meta data
     */
    public function getFileMetaAsJSON(): string
    {
        return json_encode($this->fileMeta);
    }

    /**
     * @return Collection
     */
    public function getFileMeta(): Collection
    {
        return new ArrayCollection($this->fileMeta);
    }

    /**
     * @param Collection $fileMeta
     *
     * @return UploadableFile
     */
    public function setFileMeta(Collection $fileMeta): self
    {
        $this->fileMeta = $fileMeta->toArray();

        return $this;
    }
}
