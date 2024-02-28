<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\MealMatch\Traits;

use Symfony\Component\HttpFoundation\Request;

trait Referer
{
    private function getRefererParams(Request $pRequest)
    {
        $referer = $pRequest->headers->get('referer');
        $baseUrl = $pRequest->getBaseUrl();
        $lastPath = substr($referer, strpos($referer, $baseUrl) + \strlen($baseUrl));

        return $this->get('router')->getMatcher()->match($lastPath);
    }
}
