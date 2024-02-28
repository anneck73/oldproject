<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use MMUserBundle\Entity\MMUser;

interface FinderServiceInterface
{
    public function getEntityName(): string;

    public function getEntityManager(): EntityManager;

    public function findAllByOwner(MMUser $MMUser): array;

    public function findRunningByOwner(MMUser $user): array;

    public function findCreatedByOwnerAsCollection(MMUser $user): ArrayCollection;

    public function findCreatedByOwner(MMUser $user): array;

    public function findReadyByOwner(MMUser $user): array;

    public function findAll(): array;

    public function exists(int $id): bool;
}
