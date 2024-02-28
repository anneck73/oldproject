<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Entity;

/**
 * The Interface EntityData is just a "marker" interface for doctrine entity classes inside of mealmatch.
 */
interface EntityData
{
    public function getId();

    public function getFQDN(): string;

    public function getUID(): string;
}
