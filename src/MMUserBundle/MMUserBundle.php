<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MMUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
