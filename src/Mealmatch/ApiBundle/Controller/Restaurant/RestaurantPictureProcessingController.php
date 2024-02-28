<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Controller\Restaurant;

use MangoPay\User;
use Mealmatch\ApiBundle\Controller\ApiController;
use MMUserBundle\Entity\MMRestaurantProfile;
use MMUserBundle\Entity\RestaurantImage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The RestaurantPaymentProcessing controller executes calls to Mangopay API.
 *
 * @Route("/u/restaurantpayment/manager")
 * @Security("has_role('ROLE_RESTAURANT_USER')")
 */
class RestaurantPictureProcessingController extends ApiController
{
    /**
     * Add's a new RestaurantImage to the RestaurantProfile and returns to restaurant profile manager tab 4.
     *
     * @Route("/addPicture", name="restaurant_picture_processing_add_picture", methods={"POST"})
     */
    public function addRestaurantPicture(Request $request)
    {
        // The RestaurantImage to take the new Picture.
        $image = new RestaurantImage();

        /** @var MMRestaurantProfile $restaurantProfile */
        $restaurantProfile = $this->getUser()->getRestaurantProfile();

        // Maximum 5 Pictures.
        if (5 === $restaurantProfile->getPictures()->count()) {
            //@todo: use translation token for error message
            $this->addFlash('warning', 'Es sind max. 5 Bilder pro Restaurant möglich!');

            return $this->redirectToRoute('restaurant_profile_manager_edit_pics');
        }

        $form = $this->createForm('Mealmatch\ApiBundle\Form\RestaurantProfile\RestaurantImageType', $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $restaurantProfile->addPicture($image);
            $em = $this->getDoctrine()->getManager();
            $em->persist($image);
            $em->persist($restaurantProfile);
            $em->flush();
        }

        return $this->redirectToRoute('restaurant_profile_manager_edit_pics');
    }
}
