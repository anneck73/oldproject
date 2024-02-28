<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMWebFrontBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * This controller handles all actions of the Public-Contact-Form. (Kontaktformular).
 */
class ContactFormController extends Controller
{
    /**
     * Renders the public contact form.
     *
     * @param Request $request
     *
     * @Route("/contact", name="contact_show_form")
     * @Method({"GET", "POST"})
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function showContactFormAction(Request $request)
    {
        // Create the form according to the FormType created previously.
        // And give the proper parameters
        $form = $this->createForm('MMWebFrontBundle\Form\ContactFormType');

        if ($request->isMethod('POST')) {
            // Refill the fields in case the form is not valid.
            $form->handleRequest($request);

            if ($form->isValid()) {
                // Send mail
                if ($this->sendEmail($form->getData())) {
                    // Everything OK, redirect to wherever you want ! :

                    return $this->redirectToRoute('contact_email_send');
                }
                // An error ocurred, handle
                $this->get('logger')->addAlert('Failed to send ContactFormEmail!');
            }
        }

        $viewTitle = $this->get('translator')->trans('contact.form.title', array(), 'Mealmatch');
        $viewData = array(
            'title' => $viewTitle,
        );

        return $this->render(
            '@WEBUI/Contact/contactForm.html.twig',
            array(
                'contact_form' => $form->createView(),
                'viewData' => $viewData,
            )
        );
    }

    /**
     * Is rendered after the Email has been send.
     *
     * @Route("/contactEmailSend", name="contact_email_send")
     * @Method({"GET", "POST"})
     */
    public function showEmailSend()
    {
        return $this->render('@WEBUI/Contact/contactFormSend.html.twig');
    }

    /**
     * Actually sends the email using SWIFT.
     *
     * @param array $data data consumed in the message send
     *
     * @return int the number of recipients accepted for delivery
     */
    private function sendEmail(array $data)
    {
        $swift = $this->get('swiftmailer.mailer');

        $message = \Swift_Message::newInstance()
                                 ->setSubject('[Kontakt-Formular]: '.$data['subject'])
                                 ->setFrom('mailer@mealmatch.de')
                                 ->setTo('contact.form@mealmatch.de')
                                 ->setBody(
                                     $this->renderView(
                                         '@Api/Emails/Contact.html.twig',
                                         array(
                                             'FORM_DATA' => $data,
                                         )
                                     ),
                                     'text/html'
                                 )
        ;

        return $swift->send($message);
    }
}
