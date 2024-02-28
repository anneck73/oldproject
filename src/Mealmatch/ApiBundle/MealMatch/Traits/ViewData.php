<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\MealMatch\Traits;

use Mealmatch\ApiBundle\ApiConstants;
use Symfony\Component\HttpFoundation\Request;

/**
 * This trait enables a uniform way to handle view data used in all TWIG templates.
 */
trait ViewData
{
    /** @var array $viewData */
    private $viewData = array(
        'mealmatch' => array(
            'version' => ApiConstants::VERSION,
        ),
    );

    public function initViewData(Request $request)
    {
        $currentRoute = $request->attributes->get('_route');
        $this->viewData[ApiConstants::CURRENT_ROUTE] = $currentRoute;
    }

    /**
     * @return array
     */
    public function getViewData(): array
    {
        return $this->viewData;
    }

    public function addObjectToViewData(string $dataKey, object $dataValue)
    {
        $this->viewData[$dataKey] = $dataValue;

        return $this->viewData;
    }

    public function addStringToViewData(string $dataKey, string $dataValue): array
    {
        $this->viewData[$dataKey] = $dataValue;

        return $this->viewData;
    }

    public function getViewDataJson(): string
    {
        return json_encode($this->viewData);
    }

    public function getViewDataString(): string
    {
        return ucwords(implode('_', $this->viewData));
    }
}
