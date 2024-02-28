<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Entity;
use Mealmatch\ApiBundle\Entity\EntityData;
use Mealmatch\ApiBundle\Exceptions\ServiceDataException;

/**
 * Add's data handling features to the implementing class.
 * Add's error handling features for the data.
 *
 * It SHOULD abstract all interactions with entities for a given service class.
 * This is the BusinessModel-Part of all service specific logic contained in service classes e.g. 'services'.
 *
 * @todo: The "specification" part is still WiP. As of now a simple string identifies the specification.
 */
abstract class AbstractServiceDataManager implements ServiceDataSpecification
{
    /**
     * Super internal data storage, used by all implementing classes!
     *
     * @var ArrayCollection
     */
    protected $data;

    /**
     * AbstractServiceDataManager constructor.
     * The implementing method SHOULD overwrite this constructor:
     *    parent::__construct('[SPECIFICATION]', new [ENTITY OBJECT]);.
     *
     * @param string     $specification
     * @param EntityData $entity
     *
     * @throws ServiceDataException
     */
    public function __construct(string $specification, EntityData $entity)
    {
        $this->data = new ArrayCollection();
        $this->data->set(ServiceDataSpecification::SPECIFICATION_KEY, $specification);
        $this->data->set(ServiceDataSpecification::VALIDATION_KEY, false);
        $this->data->set(ServiceDataSpecification::MANAGED_ENTITY_KEY, false);
        $this->data->set(ServiceDataSpecification::ERRORS_KEY, new ArrayCollection());
        $this->data->set(ServiceDataSpecification::ENTITIES_KEY, new ArrayCollection());
        $this->addEntity($specification, $entity);
    }

    /**
     * Returns CLASS and Specification.
     *
     * @return string
     */
    public function __toString(): string
    {
        return __CLASS__.$this->getSpecification();
    }

    /**
     * Adds the entity to the internal entities collection.
     *
     * @param string $entityKey the key to use to identify within the entities collection
     * @param object $entity    the entity to be stored
     *
     * @throws ServiceDataException
     */
    public function addEntity(string $entityKey, EntityData $entity)
    {
        /** @var ArrayCollection $entities */
        $entities = $this->getEntities();

        if (!$entities->containsKey($entityKey)) {
            $entities->set($entityKey, $entity);
        } else {
            throw new ServiceDataException('Can not add the same entityKey twice! ('.$entityKey.')');
        }
    }

    /**
     * Returns all internal entities.
     *
     * @return Collection
     */
    public function getEntities(): Collection
    {
        return $this->data->get(ServiceDataSpecification::ENTITIES_KEY);
    }

    /**
     * Returns the specification string used during construction.
     *
     * @return string the specification used
     */
    public function getSpecification(): string
    {
        if ($this->data->containsKey(ServiceDataSpecification::SPECIFICATION_KEY)) {
            return $this->data->get(ServiceDataSpecification::SPECIFICATION_KEY);
        }

        return ServiceDataSpecification::UNDEFINED;
    }

    /**
     * @param string $entityKey
     *
     * @return bool
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     */
    public function isManaged(string $entityKey): bool
    {
        if ($this->getEntities()->containsKey($entityKey)) {
            /** @var EntityData $entity */
            $entity = $this->getEntities()->get($entityKey);

            return null !== $entity->getId();
        }
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $entityKey the entity key used to find the it
     *
     * @throws ServiceDataException
     *
     * @return EntityData the doctrine entity found by key
     */
    public function getEntity(string $entityKey): EntityData
    {
        $entities = $this->getEntities();

        if ($entities->containsKey($entityKey)) {
            return $entities->get($entityKey);
        }

        throw new ServiceDataException(sprintf('The entityKey (%s) does not exist!', $entityKey));
    }

    /**
     * @return bool
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     */
    public function isValid(): bool
    {
        if ($this->data->containsKey(ServiceDataSpecification::VALIDATION_KEY)) {
            return $this->data->get(ServiceDataSpecification::VALIDATION_KEY);
        }

        return false;
    }

    /**
     * Sets the internal data validation key to the specified value.
     *
     * @param bool $valid the value to set
     */
    public function setValidity(bool $valid): void
    {
        $this->data->set(ServiceDataSpecification::VALIDATION_KEY, $valid);
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @return ArrayCollection
     */
    public function getErrors(): ArrayCollection
    {
        return $this->data->get(ServiceDataSpecification::ERRORS_KEY);
    }

    public function addError(string $error): void
    {
        $errors = $this->getData(ServiceDataSpecification::ERRORS_KEY);
        $errors->add($error);
        $this->setData(ServiceDataSpecification::ERRORS_KEY, $errors);
    }

    public function getData(string $dataKey)
    {
        return $this->data->get($dataKey);
    }

    public function setData(string $dataKey, $dataValue): void
    {
        $this->data->set($dataKey, $dataValue);
    }

    public function getErrorsAsJSON(): string
    {
        return json_encode($this->getErrors()->toArray());
    }

    /**
     * Returns the NAME (string) of the class contained in the ServiceData by its specification;.
     *
     * @throws ServiceDataException
     *
     * @return string the name of the class contained
     */
    protected function getEntityClass(): ?string
    {
        return \get_class($this->getEntity($this->getSpecification()));
    }

    /**
     * Sets internal specification data entity using the "specification" as the entityKey.
     *
     * @param EntityData $entity
     */
    protected function setEntity(EntityData $entity): void
    {
        $this->getEntities()->set($this->getSpecification(), $entity);
    }
}
