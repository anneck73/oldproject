<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Model;

use Mealmatch\ApiBundle\Entity\Community\Community;
use Mealmatch\ApiBundle\Entity\EntityData;
use Mealmatch\ApiBundle\Exceptions\MealmatchException;
use Mealmatch\ApiBundle\Exceptions\ServiceDataException;

class CommunityData extends AbstractJSONDataManager
{
    public const DATA_TYPE_ENTITY = 'DATA_TYPE_ENTITY';

    public function __construct(Community $cEntityData)
    {
        parent::__construct();
        $this->data->set(self::DATA_TYPE_ENTITY, $cEntityData);
    }

    /**
     * @param string     $dataKey
     * @param EntityData $entityData must be an instance of Entity\Community\Community;
     *
     * @throws ServiceDataException is thrown if the entity data does not match the required class
     */
    public function addEntityData(string $dataKey, EntityData $entityData): void
    {
        if ($entityData instanceof Community) {
            $this->processEntityData($entityData);

            return;
        }

        throw new ServiceDataException(sprintf('Unsupported EntityData Class for Key: %s', $dataKey));
    }

    /**
     * @param string $dataKey
     *
     * @throws MealmatchException
     *
     * @return string
     */
    public function getDataAsJson(string $dataKey = self::DATA_TYPE_ENTITY): string
    {
        if (!$this->data->containsKey($dataKey)) {
            throw new MealmatchException('ERR: NO DATA KEY: '.$dataKey.'found!');
        }
        $cData = $this->data->get($dataKey);

        return CommunityDataJSONProcessor::toJSON($cData);
    }

    public function getDataAsEntityData(string $dataKey = self::DATA_TYPE_ENTITY): EntityData
    {
        return $this->data->get($dataKey);
    }

    private function processEntityData(Community $entityData): void
    {
        $this->data->set(self::DATA_TYPE_ENTITY, $entityData);
    }
}
