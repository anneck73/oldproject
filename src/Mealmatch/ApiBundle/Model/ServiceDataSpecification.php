<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Model;

use Doctrine\Common\Collections\Collection;
use Mealmatch\ApiBundle\Entity\EntityData;

/**
 * The Interface ServiceDataSpecification binds "data" (entities) to a "specification" qualified via a string.
 *
 * The implementing methods provide access, information and validation features and return the Entities associated with
 * the specification during creation of "Service Specific Data".
 */
interface ServiceDataSpecification
{
    const SPECIFICATION_KEY = 'SPECIFICATION';
    const ENTITIES_KEY = 'ENTITIES';
    const MANAGED_ENTITY_KEY = 'MANAGED';
    const VALIDATION_KEY = 'VALID';
    const UNDEFINED = '!UNDEFINED!';
    const ERRORS_KEY = 'ERRORS';

    public function addEntity(string $entityKey, EntityData $entity);

    public function getEntity(string $entityKey): EntityData;

    public function getEntities(): Collection;

    public function getSpecification(): string;

    public function isValid(): bool;

    public function isManaged(string $entityKey): bool;
}
