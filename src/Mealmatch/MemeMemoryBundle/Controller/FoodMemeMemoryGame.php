<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\MemeMemoryBundle\Controller;

use Mealmatch\ApiBundle\Controller\ApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class FoodMemeMemoryGame does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 *
 * @Route("/FMMG")
 */
class FoodMemeMemoryGame extends ApiController
{
    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route('/start')
     *
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        return $this->render('@MealmatchMemeMemory/Memory/memory.html.twig');
    }
}
