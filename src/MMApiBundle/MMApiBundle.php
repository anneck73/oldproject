<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMApiBundle;

@trigger_error('The '.__NAMESPACE__.'\ is deprecated since version 0.2 and will be removed in 1.0. '.
    'Use the Mealmatch\ApiBundle instead.', E_USER_DEPRECATED);

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @deprecated since version 0.2, to be removed in 1.0. Use mealmatch\ApiBundle instead.
 */
class MMApiBundle extends Bundle
{
    const BUNDLE_NAME = 'MMApiBundle';
}
