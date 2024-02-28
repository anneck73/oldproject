<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMWebFrontBundle\Controller;

use Mealmatch\ApiBundle\Controller\ApiController;
use Mealmatch\UIPublicBundle\Controller\PublicStaticPagesController;
use MMUserBundle\Entity\MMUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * This is a simple Demo controller.
 *
 * @todo: Make it only visible for "demo" user.
 *
 * @Route("/demo/template")
 */
class LayoutDemoController extends ApiController
{
    /**
     * Loads a Demo/$template.html.twig with some DEMO values.
     *
     *
     * @Route("/{template}", name="layout_main",
     *     defaults={"template": "demo1"},
     *     requirements={"_locale": "de"},
     *     options={"utf8": true}
     *     )
     *
     * @param Request $pRequest
     *
     * @return mixed
     */
    public function layoutMainDemoAction(Request $pRequest, string $template)
    {
        $userProfile = new MMUser();
        $regForm = $this->createForm(PublicStaticPagesController::FORM_TYPE_REGISTRATION, $userProfile);
        $viewData = array(
            'title' => 'DEMO TITLE',
        );

        return $this->render(
            '@WEBUI/Demo/'.$template.'.html.twig',
            array(
                PublicStaticPagesController::TEMPLATE_VAR_REG_FORM => $regForm->createView(),
                'viewData' => $viewData,
            )
        );
    }
}
