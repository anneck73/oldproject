<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\MealMatch;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;
use Mealmatch\ApiBundle\Entity\EntityData;
use ReflectionObject;

/**
 * Standalone Helper using entity manager.
 */
class EntityHelper
{
    /**
     * @todo: Finish PHPDoc!
     *
     * @var EntityManager
     */
    private $entityManager;

    /**
     * EntityHelper constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Uses entityClass to find all getter who are not null AND counts collection sizes.
     * It uses "addToAllFields" to manually set the sum of all collections in order to reach 100%.
     * Note: If you dont pre calculate the target size of your collections you can get more or less
     * then 100%.
     *
     * @param EntityData $entityData         checked for all fields not null and counts collections
     * @param string     $entityClass        the class to search for getters
     * @param int        $addToAllFieldCount the number of items expected in the collections
     *
     * @return float the percentage "filled"
     */
    public function getPercentageFilled(EntityData $entityData, string $entityClass, int $addToAllFieldCount = 0): float
    {
        $entityManager = $this->entityManager;

        $properties = $entityManager->getClassMetadata($entityClass)->getFieldNames();
        $allFieldsCount = \count($properties) + $addToAllFieldCount;
        $output = array_merge(
            $properties,
            $entityManager->getClassMetadata($entityClass)->getAssociationNames()
        );

        $reflector = new ReflectionObject($entityData);
        $count = 0;

        foreach ($output as $property) {
            $method = $reflector->getMethod('get'.ucfirst($property));
            $method->setAccessible(true);
            $result = $method->invoke($entityData);
            if ($result instanceof PersistentCollection) {
                $collectionReflector = new \ReflectionObject($result);
                $method = $collectionReflector->getMethod('count');
                $method->setAccessible(true);
                $result = $method->invoke($result);
                $count += $result;
            } else {
                null === $result ?: $count++;
            }
        }

        return $count / $allFieldsCount * 100;
    }
}
