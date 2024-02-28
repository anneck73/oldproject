<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Controller\JoinRequest;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Controller\ApiController;
use Mealmatch\ApiBundle\Entity\Meal\MealJoinRequest;
use Mealmatch\ApiBundle\Form\JoinRequest\JoinRequestType;
use MMApiBundle\Entity\JoinRequest;
use MMApiBundle\Entity\Meal;
use MMApiBundle\Exceptions\MMPaymentException;
use MMApiBundle\MealMatch\FlashTypes;
use MMUserBundle\Entity\MMUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Joinrequest controller.
 *
 * @Route("api/joinrequest")
 * @Security("has_role('ROLE_USER')")
 */
class JoinRequestController extends ApiController
{
    /**
     * Denies the join request ...
     *
     * @Route("/{hash}/deny", name="joinrequest_deny")
     * @Method({"GET", "POST"})
     *
     * @param Request         $request
     * @param MealJoinRequest $joinRequest
     *
     * @return RedirectResponse|Response
     */
    public function denyAction(Request $request, MealJoinRequest $joinRequest)
    {
        $deniedForm = $this->createForm('MMApiBundle\Form\JoinRequestDeniedType', $joinRequest);
        $deniedForm->handleRequest($request);

        if ($deniedForm->isSubmitted() && $deniedForm->isValid()) {
            $joinRequest->setDenied(true);
            $meal = $joinRequest->getMeal();

            $message = \Swift_Message::newInstance()
                ->setSubject('[Anfrage Abgeleht] - '.$meal->getTitle())
                ->setFrom('mailer@mealmatch.de')
                ->setTo($joinRequest->getCreatedBy()->getEmail())
                ->setBody(
                    $this->renderView(
                        '@API/Emails/JoinRequest-Denied.html.twig',
                        array(
                            'JR' => $joinRequest,
                            'MEAL' => $meal,
                            'HOST' => $meal->getHost(),
                        )
                    ),
                    'text/html'
                );
            $this->get('swiftmailer.mailer')->send($message);
            $guestUser = $joinRequest->getCreatedBy();
            $this->sendSystemMessage(
                $joinRequest->getCreatedBy(), $meal->getHost(), '[Anfrage abgelehnt]'.' '.$meal->getTitle(), $this->renderView(
                'ApiBundle:SystemMessages:JoinRequest-Denied-NoticeToGuest.html.twig',
                array(
                    'JR' => $joinRequest,
                    'MEAL' => $meal,
                    'GUEST' => $guestUser,
                    'HOST' => $meal->getHost(),
                )
            )
            );
            $this->sendSystemMessage(
                $meal->getHost(), $this->getUser(), '[Anfrage abgelehnt]'.' '.$meal->getTitle(), $this->renderView(
                'ApiBundle:SystemMessages:JoinRequest-Denied-NoticeToHost.html.twig',
                array(
                    'JR' => $joinRequest,
                    'MEAL' => $meal,
                    'GUEST' => $guestUser,
                    'HOST' => $meal->getHost(),
                )
            )
            );
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('joinrequest_index');
        }

        return $this->render(
            '@WEBUI/JoinRequest/Deny/deny.html.twig',
            array(
                'joinRequest' => $joinRequest,
                'denied_form' => $deniedForm->createView(),
            )
        );
    }

    /**
     * Accepts the join request ...
     *
     * @Route("/{hash}/accept", name="joinrequest_accept")
     * @Method({"GET", "POST"})
     *
     * @param Request         $request
     * @param MealJoinRequest $joinRequest
     *
     * @return RedirectResponse|Response
     */
    public function acceptAction(Request $request, MealJoinRequest $joinRequest)
    {
        if (ApiConstants::JOIN_REQ_STATUS_ACCEPTED === $joinRequest->getStatus()) {
            $this->addFlash('info', 'JoinRequest can only be accepted once!');

            return $this->redirectToRoute('joinrequest_index');
        }
        if (ApiConstants::JOIN_REQ_STATUS_DENIED === $joinRequest->getStatus()) {
            $this->addFlash('danger', 'JoinRequest has already been denied!');

            return $this->redirectToRoute('joinrequest_index');
        }

        $accForm = $this->createForm(
            'MMApiBundle\Form\JoinRequestAcceptedType',
            $joinRequest
        );
        $accForm->handleRequest($request);

        if ($accForm->isSubmitted() && $accForm->isValid()) {
            $joinRequest->setAccepted(true);

            $this->getDoctrine()->getManager()->flush();

            $meal = $joinRequest->getBaseMeal();

            $messageSubject = $this->getTranslation('joinrequest.accepted.mail.subject');

            // Email to guest, the creator of the join request ...
            $message = \Swift_Message::newInstance()
                ->setSubject($messageSubject.$meal->getTitle())
                ->setFrom('mailer@mealmatch.de')
                ->setTo($joinRequest->getCreatedBy()->getEmail())
                ->setBody(
                    $this->renderView(
                        'ApiBundle:Emails:JoinRequest-Accepted.html.twig',
                        array(
                            'JR' => $joinRequest,
                            'MEAL' => $meal,
                            'HOST' => $meal->getHost(),
                        )
                    ),
                    'text/html'
                );
            $this->get('swiftmailer.mailer')->send($message);

            // SystemMessage to guest, the creator of the join request ...
            $this->sendSystemMessage(
                $joinRequest->getCreatedBy(), $meal->getHost(), $messageSubject.' '.$meal->getTitle(), $this->renderView(
                'ApiBundle:SystemMessages:JoinRequest-Accepted-NoticeToCreator.html.twig',
                array(
                    'JR' => $joinRequest,
                    'MEAL' => $meal,
                    'GUEST' => $joinRequest->getCreatedBy(),
                    'HOST' => $meal->getHost(),
                )
            )
            );

            return $this->redirectToRoute('joinrequest_index');
        }

        return $this->render(
            '@WEBUI/JoinRequest/Accept/accept.html.twig',
            array(
                'joinRequest' => $joinRequest,
                'accepted_form' => $accForm->createView(),
            )
        );
    }

    /**
     * Pays the the "accepted" join request ...
     * ...in fact it forwards to meal:addGuest.
     *
     *
     * @Route("/{hash}/payed", name="joinrequest_payed")
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     * @param string  $hash
     *
     *@throws MMPaymentException
     *
     * @return RedirectResponse|Response
     */
    public function payedAction(Request $request, string $hash)
    {
        $em = $this->get('doctrine.orm.default_entity_manager');

        /** @var MealJoinRequest $joinRequest */
        $joinRequest = $em->getRepository('ApiBundle:Meal\MealJoinRequest')->findOneBy(
            array(
                'hash' => $hash,
            )
        );

        if (null === $joinRequest) {
            $this->addFlash('danger', 'FAILED: JoinReq missing!');
            $this->get('logger')->addCritical(
                sprintf('PAYED Failed! JoinRequest Missing! hash: %s', $hash)
            );
            throw new MMPaymentException('JoinRequest not found!');
        }

        if ($joinRequest->getCreatedBy() !== $this->getUser()) {
            $this->addFlash('danger', 'FAILED: This JoinRequest can only be payed by the issuer!');

            return $this->redirectToRoute('joinrequest_index');
        }

        if (ApiConstants::JOIN_REQ_STATUS_ACCEPTED !== $joinRequest->getStatus()) {
            $this->addFlash('danger', 'FAILED: A JoinRequest can only be payed if accepted!');

            return $this->redirectToRoute('joinrequest_index');
        }
        if (ApiConstants::JOIN_REQ_STATUS_PAYED === $joinRequest->getStatus()) {
            $this->addFlash('danger', 'FAILED: A JoinRequest can only be payed once!');

            return $this->redirectToRoute('joinrequest_index');
        }

        return $this->forward('MMApiBundle:Meal:addGuest', array('id' => $joinRequest->getMeal()->getId()));
    }

    /**
     * Lists all joinRequest of the logged in user ...
     *
     * @Route("/", name="joinrequest_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var MMUser $user */
        $user = $this->getUser();

        $usersJoinedProMeals = $em->getRepository('ApiBundle:Meal\BaseMealTicket')->findBy(array('guest' => $user->getId()));

        $guestsHomeMealTickets = $em->getRepository('ApiBundle:Meal\BaseMealTicket')->findBy(array('host' => $user->getId()));

        $joinReqG = $em->getRepository('ApiBundle:Meal\MealJoinRequest')->findBy(
            array(
                'createdBy' => $user->getId(),
            )
        );

        $joinReqH = array();
        foreach (
            $em->getRepository('ApiBundle:Meal\HomeMeal')
                ->findBy(array('createdBy' => $user->getId())) as $baseMeal
        ) {
            $joinReqH = array_merge($joinReqH, $baseMeal->getJoinRequests()->toArray());
        }

        $viewData = array(
          'title' => $this->get('translator')->trans('joinrequest.index.title', array(), 'Mealmatch'),
        );

        return $this->render(
            '@WEBUI/JoinRequest/index.html.twig',
            array(
                'usersJoinedProMeals' => $usersJoinedProMeals,
                'guestsHomeMealTickets' => $guestsHomeMealTickets,
                'joinReqG' => $joinReqG,
                'joinReqH' => $joinReqH,
                'viewData' => $viewData,
                'selectedTab' => $this->getSelectedTab($request),
            )
        );
    }

    /**
     * Deletes a joinRequest entity.
     *
     * @Route("/{id}", name="joinrequest_delete")
     * @Method("DELETE")
     *
     * @param Request         $request
     * @param MealJoinRequest $joinRequest
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, MealJoinRequest $joinRequest): RedirectResponse
    {
        $form = $this->createDeleteForm($joinRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($joinRequest);
            $em->flush();
        }

        return $this->redirectToRoute('joinrequest_index');
    }

    /**
     * @Route("/{id}/addJoinRequest", name="meal_add_join_request", requirements={"id" = "\d+"}))
     * @Method({"POST","GET"})
     *
     * @param Request $request
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     *
     * @return RedirectResponse|Response
     */
    public function addJoinRequest(Request $request)
    {
        // Initialize self ...
        $this->init();
        // set current locale
        $this->translator->setLocale($request->getLocale());
        // error messages, localized
        $joinYourOwnMeal = $this->trans('meal.join.own.meal');
        $requestToJoinEmailSubject = $this->trans('joinrequest.created.mail.subject');

        /** @var int $id */
        $id = $request->get('id');
        /** @var Meal $meal The Meal this joinRequest is about ... */
        $meal = $this->em->getRepository('ApiBundle:Meal\HomeMeal')->find($id);

        if (null === $meal) {
            throw new NotFoundHttpException('Meal with ID:$id does not exist!');
        }

        /** @var JoinRequest|ArrayCollection $existingJR */
        $existingJR = $meal->getJoinRequests();

        /** @var JoinRequest $joinReq */
        foreach ($existingJR as $joinReq) {
            if ($joinReq->getCreatedBy() === $this->getUser()) {
                $noticeToUser = $this->trans('meal.already.requested.to.join');
                $this->addFlash(
                    FlashTypes::$WARNING,
                    $noticeToUser
                );

                // The current user already started a JR
                return $this->redirectToRoute('search_do');
            }
        }

        /** @var MMUser $guest The current User is the Guest asking the Host */
        $guest = $this->getUser();

        // Just to be sure ...
        // Check if host equals guest, this is not allowed :)
        if ($meal->getHost() === $guest) {
            $this->addFlash(
                FlashTypes::$WARNING,
                $joinYourOwnMeal
            );

            return $this->redirect(
                $this->generateUrl('joinrequest_index')
            );
        }

        /** @var MealJoinRequest $joinRequest */
        $joinRequest = new MealJoinRequest();

        $form = $this->createForm(JoinRequestType::class, $joinRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em = $this->getDoctrine()->getManager();

            $joinRequest->setBaseMeal($meal);

            $this->em->persist($joinRequest);
            $this->em->flush();
            // Get a fully populated user inkl. profile, etc ...
            $guestUser = $this->em->find('MMUserBundle:MMUser', $guest->getId());
            // Notification ...
            $message =
                \Swift_Message::newInstance()
                    ->setSubject($requestToJoinEmailSubject.$meal->getTitle())
                    ->setFrom('mailer@mealmatch.de')
                    ->setTo($meal->getHost()->getEmail())
                    ->setBody(
                        $this->renderView(
                            'ApiBundle:Emails:JoinRequest-Created.html.twig',
                            array(
                                'JR' => $joinRequest,
                                'MEAL' => $meal,
                                'GUEST' => $guestUser,
                            )
                        ),
                        'text/html'
                    );
            $this->get('swiftmailer.mailer')->send($message);

            $this->sendSystemMessage(
                $this->getUser(),
                $meal->getHost(),
                $requestToJoinEmailSubject.' '.$meal->getTitle(),
                $this->renderView(
                'ApiBundle:SystemMessages:JoinRequest-Created-NoticeToCreator.html.twig',
                array(
                    'JR' => $joinRequest,
                    'MEAL' => $meal,
                    'GUEST' => $guestUser,
                )
            )
            );
            $this->sendSystemMessage(
                $meal->getHost(), $this->getUser(), $requestToJoinEmailSubject.' '.$meal->getTitle(), $this->renderView(
                'ApiBundle:SystemMessages:JoinRequest-Created-NoticeToHost.html.twig',
                array(
                    'JR' => $joinRequest,
                    'MEAL' => $meal,
                    'GUEST' => $guestUser,
                )
            )
            );

            // Back to where we came from ...
            return $this->redirectToRoute('joinrequest_index');
        }

        return $this->render(
            '@WEBUI/JoinRequest/New/new.html.twig',
            array(
                'joinRequest' => $joinRequest,
                'meal' => $meal,
                'form' => $form->createView(),
                'viewData' => array('title' => '--DEFAULT--'),
            )
        );
    }

    /**
     * Creates a form to delete a joinRequest entity.
     *
     * @param MealJoinRequest $joinRequest The joinRequest entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(MealJoinRequest $joinRequest)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('joinrequest_delete', array('id' => $joinRequest->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }

    private function sendSystemMessage($receiver, $sender, $subject, $body)
    {
        $message = $this->get('fos_message.composer')->newThread()
            ->addRecipient($receiver)
            ->setSubject($subject)
            ->setSender($sender)
            ->setBody($body)
            ->getMessage();

        $this->get('fos_message.sender')->send($message);
    }

    private function getTranslation(string $id, array $options = array())
    {
        return $this->get('translator')->trans(
            $id,
            $options,
            'Mealmatch'
        );
    }
}
