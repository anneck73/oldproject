<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMWebFrontBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class MMCardController does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 * @Route("/p/card")
 */
class MMCardController extends Controller
{
    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param $name
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/random")
     */
    public function showRandomAction()
    {
        $cardNumber = random_int(1, 141);

        return $this->render('@WEBUI/Cards/card.html.twig', array('cardNumber' => $cardNumber));
    }
}
