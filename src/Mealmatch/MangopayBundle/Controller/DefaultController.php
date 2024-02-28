<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\MangopayBundle\Controller;

use Mealmatch\ApiBundle\Controller\ApiController;

/**
 * Class DefaultController.
 */
class DefaultController extends ApiController
{
    public function indexAction()
    {
        return $this->render('MealmatchMangopayBundle:Default:index.html.twig');
    }
}
