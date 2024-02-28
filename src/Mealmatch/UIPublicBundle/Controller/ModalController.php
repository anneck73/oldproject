<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\UIPublicBundle\Controller;

use Mealmatch\ApiBundle\Controller\ApiController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * This class renders a modal twig template.
 *
 * @Route("/modal")
 */
class ModalController extends ApiController
{
    /**
     * Loads a modal template by name.
     *
     * @Route("/{modalName}", name="modal_route", methods={"GET"})
     *
     * @param string $modalName
     *
     * @return Response
     */
    public function showModalAction(string $modalName)
    {
        return $this->render('@WEBUI/Modals/'.$modalName.'.html.twig');
    }

    /**
     * Loads a modal template by name.
     *
     * @Route("/load/{template}", name="modal_load_template",
     *     requirements={"template": ".+"},
     *     defaults={"template": "Modals/default"}, methods={"GET"})
     *
     * @param string $template
     *
     * @return Response
     */
    public function showModalFromTemplateAction(Request $request, string $template)
    {
        $request->setLocale('de');

        return $this->render('@WEBUI/Modals/'.$template.'.html.twig');
    }
}
