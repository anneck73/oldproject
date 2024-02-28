<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Model;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Mealmatch\ApiBundle\Exceptions\MealmatchException;

class CommunityDataJSONProcessor
{
    /** @var EntityManager $entityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param CommunityData $communityData
     *
     * @throws MealmatchException
     *
     * @return string
     */
    public function toJSON(CommunityData $communityData): string
    {
        try {
            $reflectionEntity = new \ReflectionClass($communityData);
            $shortName = $reflectionEntity->getShortName();
        } catch (\ReflectionException $e) {
            throw new MealmatchException('ERR:'.$e->getMessage());
        }

        $jsonData = array(
            'UUID' => $communityData->getDataAsEntityData()->getUID(),
            'Namespace' => $communityData->getDataAsEntityData()->getFQDN(),
            'ShortName' => $shortName,
        );

        $simpleArrayData = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from('ApiBundle:Community\Community', 'community\community')
            ->where('community\community = :c')
            ->setParameter('c', $communityData->getDataAsEntityData())
            ->getQuery()->getResult(Query::HYDRATE_SIMPLEOBJECT);

        $jsonData[] = array(
            'object-data' => json_encode($simpleArrayData),
        );

        return json_encode($jsonData);
    }
}
