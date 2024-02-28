<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\UIPublicBundle\Controller;

use Mealmatch\ApiBundle\Controller\ApiController;
use MMUserBundle\Entity\MMUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PublicStaticPagesController extends ApiController
{
    public const TEMPLATE_VAR_REG_FORM = 'reg_form';
    public const FORM_TYPE_REGISTRATION = 'MMUserBundle\Form\MMRegistrationType';

    /**
     * @Route("/pdfview", name="mm_pdf_viewer")
     */
    public function pdfViewAction()
    {
        return $this->render(
            '@WEBUI/PDF/viewer.html.twig'
        );
    }

    /**
     * This Route "generates" the google verification. Multidomaintracking is done via GA JavaScript.
     *
     * @Route("/googlee636e102f25653a5.html", name="google_verfication")
     */
    public function googleHTMLVerificationAction(Request $pRequest)
    {
        return new Response('google-site-verification: googlee636e102f25653a5.html');
    }

    /**
     * @Route("/privacystatement", name="mm_privacy")
     * @Route("/datenschutzerklärung", name="mm_datenschutz")
     */
    public function privacyAction(Request $pRequest)
    {
        // Setting SEO Title
        $this->get('api.seo')->setTitle($this->trans('privacy.seo.title'));
        $this->get('api.seo')->addMeta('name', 'description',
            $this->trans('privacy.seo.description'));
        $this->get('api.seo')->addMeta('name', 'keywords',
            $this->trans('privacy.seo.keywords'));

        $userProfile = new MMUser();
        $regForm = $this->createForm(self::FORM_TYPE_REGISTRATION, $userProfile);

        return $this->render(
            '@WEBUI/StaticPages/privacy.html.twig',
            array(self::TEMPLATE_VAR_REG_FORM => $regForm->createView())
        );
    }

    /**
     * @Route("/terms", name="mm_terms")
     * @Route("/nutzungsbedingungen", name="mm_nutzungsbedingungen")
     */
    public function termsAction(Request $pRequest)
    {
        // Setting SEO Title
        $this->get('api.seo')->setTitle($this->trans('terms.seo.title'));
        $this->get('api.seo')->addMeta('name', 'description',
            $this->trans('terms.seo.description'));
        $this->get('api.seo')->addMeta('name', 'keywords',
            $this->trans('terms.seo.keywords'));

        $userProfile = new MMUser();
        $regForm = $this->createForm(self::FORM_TYPE_REGISTRATION, $userProfile);

        return $this->render(
            '@WEBUI/StaticPages/terms.html.twig',
            array(self::TEMPLATE_VAR_REG_FORM => $regForm->createView())
        );
    }

    /**
     * @todo Outdated route? Actual is /terms and /nutzungsbedingungen without /restaurant/
     *
     * @Route("/restaurant/terms", name="mm_restaurant_terms")
     * @Route("/restaurant/nutzungsbedingungen", name="mm_restaurant_nutzungsbedingungen")
     */
    public function termsRestaurantAction(Request $pRequest)
    {
        // Setting SEO Title
        $this->get('api.seo')->setTitle($this->trans('terms.seo.title'));
        $this->get('api.seo')->addMeta('name', 'description',
            $this->trans('terms.seo.description').' Restaurant');
        $this->get('api.seo')->addMeta('name', 'keywords',
            $this->trans('terms.seo.keywords'));

        $userProfile = new MMUser();
        $regForm = $this->createForm(self::FORM_TYPE_REGISTRATION, $userProfile);

        return $this->render(
            '@WEBUI/StaticPages/termsRestaurant.html.twig',
            array(self::TEMPLATE_VAR_REG_FORM => $regForm->createView())
        );
    }

    /**
     * @Route("/mangopay/terms", name="mm_mangopay_terms")
     * @Route("/mangopay/nutzungsbedingungen", name="mm_mangopay_nutzungsbedingungen")
     */
    public function termsMangopayAction(Request $pRequest)
    {
        // Setting SEO Title
        $this->get('api.seo')->setTitle($this->trans('terms.seo.title'));
        $this->get('api.seo')->addMeta('name', 'description',
            $this->trans('terms.seo.description').' Mangopay');
        $this->get('api.seo')->addMeta('name', 'keywords',
            $this->trans('terms.seo.keywords'));

        $userProfile = new MMUser();
        $regForm = $this->createForm(self::FORM_TYPE_REGISTRATION, $userProfile);

        return $this->render(
            '@WEBUI/StaticPages/termsMangopay.html.twig',
            array(self::TEMPLATE_VAR_REG_FORM => $regForm->createView())
        );
    }

    /**
     * @Route("/press", name="mm_press")
     * @Route("/presse", name="mm_press")
     */
    public function pressAction(Request $pRequest)
    {
        // Setting SEO Title
        $this->get('api.seo')->setTitle($this->trans('press.seo.title'));
        $this->get('api.seo')->addMeta('name', 'description',
            $this->trans('press.seo.description'));
        $this->get('api.seo')->addMeta('name', 'keywords',
            $this->trans('press.seo.keywords'));

        // We have a register button on every page ...
        $userProfile = new MMUser();
        $regForm = $this->createForm(self::FORM_TYPE_REGISTRATION, $userProfile);

        $variant = $this->determineVariation($pRequest);
        // Get a variation of the template if it exists
        $templateName = $this->getVariationTemplate('press.html.twig');

        // Fill standard view Data from translations ...
        $viewTitle = $this->get('translator')->trans('press.title', array(), 'Mealmatch');
        $viewText = $this->get('translator')->trans('press.text', array(), 'Mealmatch');

        $viewData = array(
            'title' => $viewTitle,
            'text' => $viewText,
        );

        return $this->render(
            $templateName,
            array(
                self::TEMPLATE_VAR_REG_FORM => $regForm->createView(),
                'viewData' => $viewData,
            )
        );
    }

    /**
     * @Route("/karriere", name="mm_karriere")
     * @Route("/career", name="mm_career")
     */
    public function careerAction(Request $pRequest)
    {
        // Setting SEO Title
        $this->get('api.seo')->setTitle($this->trans('career.seo.title'));
        $this->get('api.seo')->addMeta('name', 'description',
            $this->trans('career.seo.description'));
        $this->get('api.seo')->addMeta('name', 'keywords',
            $this->trans('career.seo.keywords'));

        $userProfile = new MMUser();
        $regForm = $this->createForm(self::FORM_TYPE_REGISTRATION, $userProfile);

        $variant = $this->determineVariation($pRequest);
        // Get a variation of the template if it exists
        $templateName = $this->getVariationTemplate('career.html.twig');

        // Fill standard view Data from translations ...
        $viewTitle = $this->get('translator')->trans('career.title', array(), 'Mealmatch');
        $viewText = $this->get('translator')->trans('career.content', array(), 'Mealmatch');

        $viewData = array(
            'title' => $viewTitle,
            'text' => $viewText,
        );

        return $this->render(
            $templateName,
            array(
                self::TEMPLATE_VAR_REG_FORM => $regForm->createView(),
                'viewData' => $viewData,
            )
        );
    }

    /**
     * @Route("/so_funktionierts", name="mm_so_funktionierts")
     * @Route("/how_it_works", name="mm_how_it_works")
     */
    public function howItWorksAction(Request $pRequest)
    {
        // Setting SEO Title
        $this->get('api.seo')->setTitle($this->trans('howItWorks.seo.title'));
        $this->get('api.seo')->addMeta('name', 'description',
            $this->trans('howItWorks.seo.description'));
        $this->get('api.seo')->addMeta('name', 'keywords',
            $this->trans('howItWorks.seo.keywords'));

        $userProfile = new MMUser();
        $regForm = $this->createForm(self::FORM_TYPE_REGISTRATION, $userProfile);

        $variant = $this->determineVariation($pRequest);
        // Get a variation of the template if it exists
        $templateName = $this->getVariationTemplate('howItWorks.html.twig');

        // Fill standard view Data from translations ...
        $viewTitle = $this->get('translator')->trans('howItWorks.title', array(), 'Mealmatch');
        $viewText = $this->get('translator')->trans('howItWorks.text', array(), 'Mealmatch');

        $viewData = array(
            'title' => $viewTitle,
            'text' => $viewText,
        );

        return $this->render(
            $templateName,
            array(
                self::TEMPLATE_VAR_REG_FORM => $regForm->createView(),
                'viewData' => $viewData,
            )
        );
    }

    /**
     * @Route("/GutGastgeben", name="mm_gut_gastgeben")
     * @Route("/HowToHost", name="mm_how_to_host")
     */
    public function howToHostAction(Request $pRequest)
    {
        // Setting SEO Title n stuff ...
        $this->get('api.seo')->setTitle($this->trans('howToHost.seo.title'));
        $this->get('api.seo')->addMeta('name', 'description',
            $this->trans('howToHost.seo.description'));
        $this->get('api.seo')->addMeta('name', 'keywords',
            $this->trans('howToHost.seo.keywords'));

        $userProfile = new MMUser();
        $regForm = $this->createForm(self::FORM_TYPE_REGISTRATION, $userProfile);

        $variant = $this->determineVariation($pRequest);
        // Get a variation of the template if it exists
        $templateName = $this->getVariationTemplate('howToHost.html.twig');

        // Fill standard view Data from translations ...
        $viewTitle = $this->get('translator')->trans('howToHost.title', array(), 'Mealmatch');
        $viewText = $this->get('translator')->trans('howToHost.text', array(), 'Mealmatch');

        $viewData = array(
            'title' => $viewTitle,
            'text' => $viewText,
        );

        return $this->render(
            $templateName,
            array(
                self::TEMPLATE_VAR_REG_FORM => $regForm->createView(),
                'viewData' => $viewData,
            )
        );
    }

    /**
     * @Route("/VertrauenUndSicherheit", name="mm_vertrauen_sicherheit")
     * @Route("/TrustAndSecurity", name="mm_trust_security")
     */
    public function trustAndSecurityAction(Request $pRequest)
    {
        // Setting SEO Title n stuff ...
        $this->get('api.seo')->setTitle($this->trans('trustAndSecurity.seo.title'));
        $this->get('api.seo')->addMeta('name', 'description',
            $this->trans('trustAndSecurity.seo.description'));
        $this->get('api.seo')->addMeta('name', 'keywords',
            $this->trans('trustAndSecurity.seo.keywords'));

        $userProfile = new MMUser();
        $regForm = $this->createForm(self::FORM_TYPE_REGISTRATION, $userProfile);

        $variant = $this->determineVariation($pRequest);
        // Get a variation of the template if it exists
        $templateName = $this->getVariationTemplate('trustAndSecurity.html.twig');

        // Fill standard view Data from translations ...
        $viewTitle = $this->get('translator')->trans('trustAndSecurity.title', array(), 'Mealmatch');
        $viewText = $this->get('translator')->trans('trustAndSecurity.text', array(), 'Mealmatch');

        $viewData = array(
            'title' => $viewTitle,
            'text' => $viewText,
        );

        return $this->render(
            $templateName,
            array(
                self::TEMPLATE_VAR_REG_FORM => $regForm->createView(),
                'viewData' => $viewData,
            )
        );
    }

    /**
     * @Route("/Aktionen", name="mm_Aktionen")
     * @Route("/aktionen", name="mm_aktionen")
     * @Route("/Events", name="mm_Events")
     * @Route("/events", name="mm_events")
     */
    public function eventsAction(Request $pRequest)
    {
        // Setting SEO Title n stuff ...
        $this->get('api.seo')->setTitle($this->trans('events.seo.title'));
        $this->get('api.seo')->addMeta('name', 'description',
            $this->trans('events.seo.description'));
        $this->get('api.seo')->addMeta('name', 'keywords',
            $this->trans('events.seo.keywords'));

        $userProfile = new MMUser();
        $regForm = $this->createForm(self::FORM_TYPE_REGISTRATION, $userProfile);

        $variant = $this->determineVariation($pRequest);
        // Get a variation of the template if it exists
        $templateName = $this->getVariationTemplate('events.html.twig');

        // Fill standard view Data from translations ...
        $viewTitle = $this->get('translator')->trans('events.title', array(), 'Mealmatch');
        $viewText = $this->get('translator')->trans('events.text', array(), 'Mealmatch');

        $viewData = array(
            'title' => $viewTitle,
            'text' => $viewText,
        );

        return $this->render(
            $templateName,
            array(
                self::TEMPLATE_VAR_REG_FORM => $regForm->createView(),
                'viewData' => $viewData,
            )
        );
    }

    /**
     * @Route("/about", name="mm_about")
     * @Route("/über", name="mm_ueber",
     *     requirements={"_locale": "de"},
     *     options={"utf8": true}
     *     )
     */
    public function aboutAction(Request $pRequest)
    {
        // Setting SEO Title n stuff ...
        $this->get('api.seo')->setTitle($this->trans('about.seo.title'));
        $this->get('api.seo')->addMeta('name', 'description',
            $this->trans('about.seo.description'));
        $this->get('api.seo')->addMeta('name', 'keywords',
            $this->trans('about.seo.keywords'));

        $userProfile = new MMUser();
        $regForm = $this->createForm(self::FORM_TYPE_REGISTRATION, $userProfile);

        // Fill standard view Data from translations ...
        $viewTitle = $this->get('translator')->trans('about.title', array(), 'Mealmatch');
        $viewText = $this->get('translator')->trans('about.text', array(), 'Mealmatch');

        $viewData = array(
            'title' => $viewTitle,
            'text' => $viewText,
        );

        return $this->render(
            '@WEBUI/StaticPages/about.html.twig',
            array(
                self::TEMPLATE_VAR_REG_FORM => $regForm->createView(),
                'viewData' => $viewData,
            )
        );
    }

    /**
     * @Route("/imprint", name="mm_imprint")
     */
    public function imprintAction(Request $pRequest)
    {
        // Setting SEO Title n stuff ...
        $this->get('api.seo')->setTitle($this->trans('imprint.seo.title'));
        $this->get('api.seo')->addMeta('name', 'description',
            $this->trans('imprint.seo.description'));
        $this->get('api.seo')->addMeta('name', 'keywords',
            $this->trans('imprint.seo.keywords'));

        $userProfile = new MMUser();
        $regForm = $this->createForm(self::FORM_TYPE_REGISTRATION, $userProfile);

        $variant = $this->determineVariation($pRequest);
        // Get a variation of the template if it exists
        $templateName = $this->getVariationTemplate('imprint.html.twig');

        // Fill standard view Data from translations ...
        $viewTitle = $this->get('translator')->trans('imprint.title', array(), 'Mealmatch');
        $viewText = $this->get('translator')->trans('imprint.fulltext', array(), 'Mealmatch');

        $viewData = array(
            'title' => $viewTitle,
            'text' => $viewText,
        );

        return $this->render(
            $templateName,
            array(
                self::TEMPLATE_VAR_REG_FORM => $regForm->createView(),
                'viewData' => $viewData,
            )
        );
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     * @Route("/becomeHost", name="become_host")
     */
    public function becomeHost()
    {
        // Setting SEO Title n stuff ...
        $this->get('api.seo')->setTitle($this->trans('becomeHost.seo.title'));
        $this->get('api.seo')->addMeta('name', 'description',
            $this->trans('becomeHost.seo.description'));
        $this->get('api.seo')->addMeta('name', 'keywords',
            $this->trans('becomeHost.seo.keywords'));

        $userProfile = new MMUser();
        $regForm = $this->createForm(self::FORM_TYPE_REGISTRATION, $userProfile);

        $variant = $this->determineVariation($pRequest);
        // Get a variation of the template if it exists
        $templateName = $this->getVariationTemplate('becomeHost.html.twig');

        // Fill standard view Data from translations ...
        $viewTitle = $this->get('translator')->trans('becomeHost.title', array(), 'Mealmatch');
        $viewText = $this->get('translator')->trans('becomeHost.text', array(), 'Mealmatch');

        $viewData = array(
            'title' => $viewTitle,
            'text' => $viewText,
        );

        return $this->render(
            $templateName,
            array(
                self::TEMPLATE_VAR_REG_FORM => $regForm->createView(),
                'viewData' => $viewData,
            )
        );
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     * @Route("/help", name="help")
     */
    public function getHelp(Request $pRequest)
    {
        // Setting SEO Title n stuff ...
        $this->get('api.seo')->setTitle($this->trans('help.seo.title'));
        $this->get('api.seo')->addMeta('name', 'description',
            $this->trans('help.seo.description'));
        $this->get('api.seo')->addMeta('name', 'keywords',
            $this->trans('help.seo.keywords'));

        $userProfile = new MMUser();
        $regForm = $this->createForm(self::FORM_TYPE_REGISTRATION, $userProfile);

        $variant = $this->determineVariation($pRequest);
        // Get a variation of the template if it exists
        $templateName = $this->getVariationTemplate('help.html.twig');

        // Fill standard view Data from translations ...
        $viewTitle = $this->get('translator')->trans('help.title', array(), 'Mealmatch');

        $viewData = array(
            'title' => $viewTitle,
        );

        return $this->render(
            $templateName,
            array(
                self::TEMPLATE_VAR_REG_FORM => $regForm->createView(),
                'viewData' => $viewData,
            )
        );
    }
}
