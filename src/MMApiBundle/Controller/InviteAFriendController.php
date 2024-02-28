<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMApiBundle\Controller;

use Mealmatch\ApiBundle\Controller\ApiController;
use MMApiBundle\Entity\Invite;
use MMApiBundle\MealMatch\FlashTypes;
use MMUserBundle\Entity\MMUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * This controller handles the "invite a friend" functionality.
 *
 * @Route("/invite")
 * @Security("has_role('ROLE_USER')")
 */
class InviteAFriendController extends ApiController
{
    const FORM_DATA_EMAIL_KEY = 'email';

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param Request $request
     *
     * @Route("/afriend", name="invite_email_show")
     * @Method({"GET", "POST"})
     */
    public function showInviteFormAction(Request $request)
    {
        // Create the form according to the FormType created previously.
        // And give the proper parameters
        $form = $this->createForm('MMWebFrontBundle\Form\InviteAFriendFormType');
        // translations ...
        $translator = $this->get('translator');
        $inviteSendFailed = $translator->trans(
            'invite.email.failed',
            array($form->getData()[self::FORM_DATA_EMAIL_KEY]),
            'Mealmatch'
        );
        $alreadyInvited = $translator->trans(
            'invite.email.already.done',
            array($form->getData()[self::FORM_DATA_EMAIL_KEY]),
            'Mealmatch'
        );
        $viewData = array(
            'title' => $this->trans('invite.friend.label'),
        );
        if ($request->isMethod('POST')) {
            // Refill the fields in case the form is not valid.
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->get('doctrine.orm.default_entity_manager');
                $emailInUse = $em->getRepository('MMApiBundle:Invite')
                    ->findOneBy(
                        array('emailUsed' => $form->getData()[self::FORM_DATA_EMAIL_KEY])
                    );

                if (null !== $emailInUse) {
                    $this->addFlash(
                        FlashTypes::$DANGER,
                        $alreadyInvited
                    );

                    return $this->render(
                        '@WEBUI/Contact/inviteForm.html.twig',
                        array(
                            'invite_form' => $form->createView(),
                            'user' => $this->getUser(),
                            'viewData' => $viewData,
                        )
                    );
                }

                // Send mail
                if ($this->sendEmail($form->getData())) {
                    /** @var Invite $invite */
                    $invite = new Invite();
                    $invite->setEmailUsed($form->getData()[self::FORM_DATA_EMAIL_KEY]);
                    $em->persist($invite);
                    $em->flush();

                    return $this->redirectToRoute('invite_email_send');
                }

                $this->addFlash(
                    FlashTypes::$DANGER,
                    $inviteSendFailed
                );
            }
        }

        return $this->render(
            '@WEBUI/Contact/inviteForm.html.twig',
            array(
                'invite_form' => $form->createView(),
                'user' => $this->getUser(),
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
     * @param Request $request
     * @Route("/send", name="invite_email_send")
     * @Method({"GET", "POST"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showEmailSend(Request $request)
    {
        return $this->render(
            '@WEBUI/Contact/inviteFormSend.html.twig',
            array(
                'user' => $this->getUser(),
            )
        );
    }

    /**
     * Private helper to send emails using swiftmailer.mailer service.
     *
     * @param $data
     *
     * @return int the number of recipients reached
     */
    private function sendEmail($data): int
    {
        $swift = $this->get('swiftmailer.mailer');
        /** @var MMUser $user */
        $user = $this->getUser();
        $message = \Swift_Message::newInstance()
            ->setSubject('[Mealmatch Einladung]')
            ->setFrom('mailer@mealmatch.de')
            ->setTo($data[self::FORM_DATA_EMAIL_KEY])
            ->setBody(
                $this->renderView(
                    '@Api/Emails/Invite.html.twig',
                    array(
                        'USER' => $user,
                        'DATA' => $data,
                    )
                ),
                'text/html'
            );

        return $swift->send($message);
    }
}
