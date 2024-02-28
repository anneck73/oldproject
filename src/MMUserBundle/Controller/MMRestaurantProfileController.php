<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Controller;

use MMUserBundle\Entity\MMRestaurantProfile;
use MMUserBundle\Entity\MMUser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Mmrestaurantprofile controller.
 *
 * @todo: Finish PHPDoc
 * @todo: Finsih editAction using LegalFile and
 *
 * @Route("restaurant")
 */
class MMRestaurantProfileController extends Controller
{
    /**
     * Displays a form to edit an existing mMUserProfile entity.
     *
     * @Route("/edit", name="restaurant_edit", methods={"GET","POST"})
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request)
    {
        /** @var MMRestaurantProfile $restaurantProfile */
        $restaurantProfile = $this->getUser()->getRestaurantProfile();

        // @todo: This is a hack, make sure all UserProfile are valid, but somewhere else!!!
        if (null === $restaurantProfile) {
            $this->get('logger')->warn(
                sprintf('Restaurantprofile was NULL. AutoFix with new for user %s', $this->getUser())
            );
            $newProfile = new MMRestaurantProfile();
            /** @var MMUser $user */
            $user = $this->getUser();
            $user->setRestaurantProfile($newProfile);
            $this->get('api.user_manager')->updateUser($user, true);
        }

        $editForm = $this->createForm('MMUserBundle\Form\MMRestaurantProfileType', $restaurantProfile);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('restaurant_edit');
        }

        return $this->render(
            '@MMUser/RestaurantProfile/edit.html.twig',
            array(
                'profile' => $restaurantProfile,
                'edit_form' => $editForm->createView(),
            )
        );
    }
}
