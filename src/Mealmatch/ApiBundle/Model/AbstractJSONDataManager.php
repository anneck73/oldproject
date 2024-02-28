<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

abstract class AbstractJSONDataManager
{
    public const DATA_TYPE_JSON = 'DATA_TYPE_JSON';

    /** @var ArrayCollection $data */
    protected $data;

    public function __construct()
    {
        $this->data = new ArrayCollection();
        $this->data->set(self::DATA_TYPE_JSON, new ArrayCollection());
        $this->data->set('ERRORS', new ArrayCollection());
        $this->data->set('TYPE', self::DATA_TYPE_JSON);
    }

    public function addErrorMessage(string $dataKey, string $errorMessage): void
    {
        $this->data->get('ERRORS')->add($dataKey, $errorMessage);
    }

    public function getJSONDataFromKey(string $dataKey): string
    {
        $this->data->get(self::DATA_TYPE_JSON)->get($dataKey);
    }

    protected function processJsonData(string $jsonData, $jsonDataKey = self::DATA_TYPE_JSON): void
    {
        // The default will process everything, without processing anything! ;)
        $this->data->set($jsonDataKey, $jsonData);
    }

    protected function addJsonData(string $dataKey, string $jsonData): void
    {
        /** @var ArrayCollection $iDataCollection */
        $iDataCollection = $this->data->get(self::DATA_TYPE_JSON);
        if (!$iDataCollection->contains($dataKey)) {
            $iDataCollection->set($dataKey, $jsonData);
        }
    }
}
