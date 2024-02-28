<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Controller;

use Mealmatch\ApiBundle\Repository\Meal\BaseMealTicketRepository;
use MMUserBundle\Entity\MMRestaurantProfile;
use MMUserBundle\Entity\MMUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use UnexpectedValueException;

/**
 * Views of the Restaurant. Not the RestaurantProfile!
 *
 * @Route("api/restaurant")
 * @Security("has_role('ROLE_RESTAURANT_USER')")
 */
class RestaurantController extends ApiController
{
    /**
     * Shows a "Restaurant" view for the owner of the restaurant.
     *
     * @param Request $request
     * @Route("/", name="restaurant_index")
     *
     * @throws UnexpectedValueException
     *
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        $userID = $this->getUser()->getId();
        $restaurantProfile =
            $this->getDoctrine()->getRepository('MMUserBundle:MMRestaurantProfile')
                ->find($userID);

        $filled = $this->getPercentageFilled($restaurantProfile, MMRestaurantProfile::class, 5);

        $viewTitle = $this->get('translator')->trans('restaurant.title', array(), 'Mealmatch');
        $viewData = array(
            'title' => $viewTitle,
        );

        /** @var MMUser $restaurantOwner */
        $restaurantOwner = $this->get('security.token_storage')->getToken()->getUser();
        /** @var BaseMealTicketRepository $ticketRepo */
        $ticketRepo = $this->getDoctrine()->getRepository('ApiBundle:Meal\BaseMealTicket');
        $ticketsSoldLast = $ticketRepo->getLastPayedByHost($restaurantOwner);
        $ticketsNext = $ticketRepo->getNextToServeByHost($restaurantOwner);

        return $this->render(
            '@WEBUI/Restaurant/index.html.twig',
            array(
                'restaurant' => $restaurantProfile,
                'percentage' => $filled,
                'viewData' => $viewData,
                'ticketsSoldLast' => $ticketsSoldLast,
                'ticketsNext' => $ticketsNext,
            )
        );
    }
}
