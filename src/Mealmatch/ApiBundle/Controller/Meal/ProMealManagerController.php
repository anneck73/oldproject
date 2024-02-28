<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Controller\Meal;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Controller\ApiController;
use Mealmatch\ApiBundle\Entity\Meal\MealEvent;
use Mealmatch\ApiBundle\Entity\Meal\MealOffer;
use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Mealmatch\ApiBundle\Form\Meal\MealEventType;
use Mealmatch\ApiBundle\Form\Meal\MealOfferType;
use Mealmatch\ApiBundle\Form\Meal\ProMealNotesType;
use Mealmatch\ApiBundle\Form\Meal\ProMealType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * The ProMealManagerController is resposible for guiding the Restaurant-Owner through the process of creating
 * ProMeals for his restaurant.
 *
 *
 * @Route("api/promeal/manager")
 * @Security("has_role('ROLE_RESTAURANT_USER')")
 */
class ProMealManagerController extends ApiController
{
    /**
     * Shows the main management interface to the user.
     *
     * @param Request $request
     * @param ProMeal $proMeal
     * @Route("/{id}/edit", name="api_promeal_manager_edit")
     * @Method("GET")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, ProMeal $proMeal)
    {
        // Verify that the meal exists ...
        if (null === $proMeal) {
            $id = $request->get('id');
            $msg = $this->get('translator')->trans('meal.not_found', array('%mealid%' => $id), 'Mealmatch');
            $this->addFlash('info', $msg);

            return $this->redirectToRoute('api_promeal_index');
        }

        $this->checkAccess($proMeal);

        if (ApiConstants::MEAL_STATUS_RUNNING === $proMeal->getStatus()) {
            $msg = $this->get('translator')->trans('meal.edit.error.is_running', array(), 'Mealmatch');
            $this->addFlash('info', $msg);

            return $this->redirectToRoute('api_promeal_index');
        }

        $renderViewData = $this->createProMealManagerEditViewData($request, $proMeal);

        return $this->render('@WEBUI/ProMeal/managerEdit.html.twig', $renderViewData->toArray());
    }

    /**
     * Shows the main management interface to the user.
     *
     * @param Request $request
     * @param ProMeal $proMeal
     * @Route("/{id}/show", name="api_promeal_manager_show")
     * @Method("GET")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Request $request, ProMeal $proMeal)
    {
//        $renderViewData = $this->createProMealManagerShowViewData($request, $proMeal);
        return $this->render('@WEBUI/ProMeal/managerShow.html.twig', array('proMeal' => $proMeal));
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param Request $request
     * @param ProMeal $proMeal
     * @Route("/{id}/updateMeal", name="api_promeal_manager_update_meal")
     * @Method("POST")
     */
    public function updateMeal(Request $request, ProMeal $proMeal)
    {
        $mealForm = $this->createForm('Mealmatch\ApiBundle\Form\Meal\ProMealType', $proMeal);
        $mealForm->handleRequest($request);

        if ($mealForm->isSubmitted() && $mealForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute(
                'api_promeal_manager_edit',
                array(
                    'id' => $proMeal->getId(),
                    'selectedTab' => '1',
                )
            );
        }
    }

    /**
     * @param Request $request
     * @param ProMeal $proMeal
     *
     * @Route("/{id}/updatePageMeal", name="api_promeal_manager_update_page_meal")
     *
     * @Method("POST")
     */
    public function updatePageMeal(Request $request, ProMeal $proMeal)
    {
        $mealForm = $this->createForm('Mealmatch\ApiBundle\Form\Meal\ProMealPageType', $proMeal);
        $mealForm->handleRequest($request);

        if ($mealForm->isSubmitted() && $mealForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute(
            'api_promeal_manager_edit',
            array(
                'id' => $proMeal->getId(),
                'selectedTab' => '1',
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
     * @param ProMeal $proMeal
     * @Route("/{id}/updateMealNotes", name="api_promeal_manager_update_mealnotes")
     * @Method("POST")
     */
    public function updateMealNotes(Request $request, ProMeal $proMeal)
    {
        $mealForm = $this->createForm('Mealmatch\ApiBundle\Form\Meal\ProMealNotesType', $proMeal);
        $mealForm->handleRequest($request);

        if ($mealForm->isSubmitted() && $mealForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute(
                'api_promeal_manager_edit',
                array(
                    'id' => $proMeal->getId(),
                    'selectedTab' => '3',
                )
            );
        }
    }

    /**
     * Adds a new MealOffer into a ProMeal.
     *
     * @Route("/{id}/addOffer", name="api_promeal_manager_addOffer")
     * @Method({"GET","POST"})
     */
    public function addOfferAction(Request $request, ProMeal $proMeal)
    {
        $mealOffer = new MealOffer();
        $mealOffer->setName('New');
        $mealOffer->setDescription('...');

        $proMeal->addMealOffer($mealOffer);

        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute(
            'api_promeal_manager_edit',
            array(
                'id' => $proMeal->getId(),
                'selectedTab' => 2,
            )
        );
    }

    /**
     * Adds a new MealEvent into a ProMeal.
     *
     * @Route("/{id}/addEvent", name="api_promeal_manager_addEvent")
     * @Method({"GET","POST"})
     */
    public function addEventAction(Request $request, ProMeal $proMeal)
    {
        $newEvent = new MealEvent();
        $newEvent->setStartDateTime(new \DateTime('now'));
        $newEvent->setEndDateTime(new \DateTime('+6 Hours'));

        $proMeal->addMealEvent($newEvent);

        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute(
            'api_promeal_manager_edit',
            array(
                'id' => $proMeal->getId(),
                'selectedTab' => 4,
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
     * @param ProMeal $proMeal
     * @param string  $myArgument with a *description* of this argument, these may also
     *                            span multiple lines
     */
    protected function checkAccess(ProMeal $proMeal): void
    {
        if ($this->getUser() !== $proMeal->getCreatedBy()) {
            $this->createAccessDeniedException('You are not the creator of '.$proMeal->getId());
        }
    }

    /**
     * @param ProMeal $proMeal
     * @param $formType
     * @param $targetRoute
     *
     * @return Form
     */
    private function createMealForm(ProMeal $proMeal): Form
    {
        $mealForm = $this->createForm(
            ProMealType::class,
            $proMeal,
            array(
                'action' => $this->generateUrl(
                    'api_promeal_manager_update_meal',
                    array('id' => $proMeal->getId())
                ),
                'method' => 'POST',
            )
        );

        return $mealForm;
    }

    /**
     * @param ProMeal $proMeal
     * @param string  $myArgument with a *description* of this argument, these may also
     *                            span multiple lines
     *
     * @return Form
     */
    private function createPageMealForm(ProMeal $proMeal): Form
    {
        $mealForm = $this->createForm(
            'Mealmatch\ApiBundle\Form\Meal\ProMealPageType',
            $proMeal,
            array(
                'action' => $this->generateUrl(
                    'api_promeal_manager_update_page_meal',
                    array('id' => $proMeal->getId())
                ),
                'method' => 'POST',
            )
        );

        return $mealForm;
    }

    private function createMealOfferUpdateForm(ProMeal $proMeal, MealOffer $mealOffer): Form
    {
        try {
            return $this->createForm(
                MealOfferType::class,
                $mealOffer,
                array(
                    'action' => $this->generateUrl(
                        'api_mealoffer_update',
                        array(
                            'id' => $mealOffer->getId(),
                            'proMealID' => $proMeal->getId(),
                            'selectedTab' => '2',
                        )
                    ),
                    'method' => 'POST',
                )
            );
        } catch (\Exception $exception) {
            die($exception->getMessage());
        }
    }

    private function createMealNotesForm(ProMeal $proMeal): Form
    {
        try {
            return $this->createForm(
                ProMealNotesType::class,
                $proMeal,
                array(
                    'action' => $this->generateUrl(
                        'api_promeal_manager_update_mealnotes',
                        array(
                            'id' => $proMeal->getId(),
                            'selectedTab' => '3',
                        )
                    ),
                    'method' => 'POST',
                )
            );
        } catch (\Exception $exception) {
            die($exception->getMessage());
        }
    }

    private function createMealEventUpdateForm(ProMeal $proMeal, MealEvent $mealEvent): Form
    {
        try {
            return $this->createForm(
                MealEventType::class,
                $mealEvent,
                array(
                    'action' => $this->generateUrl(
                        'api_mealevent_update',
                        array(
                            'id' => $mealEvent->getId(),
                            'mealID' => $proMeal->getId(),
                            'selectedTab' => '4',
                        )
                    ),
                    'method' => 'POST',
                )
            );
        } catch (\Exception $exception) {
            die($exception->getMessage());
        }
    }

    private function getMealEventsOrAddDefaults(ProMeal $proMeal): Collection
    {
        $mealEvents = $proMeal->getMealEvents();
        $defaultStartDateTime = new \DateTime('now');
        if ($proMeal->getMealEvents()->count() < 1) {
            /** @var MealEvent $mealEvent */
            $mealEvent = (new MealEvent())
                ->setStartDateTime($defaultStartDateTime);
            $mealEvents->add($mealEvent);
        } else {
            $mealEvents = $proMeal->getMealEvents();
        }
        $proMeal->setMealEvents($mealEvents);
        $this->get('doctrine.orm.entity_manager')->persist($proMeal);
        $this->get('doctrine.orm.entity_manager')->flush();

        return $mealEvents;
    }

    /**
     * Returns the MealOffers or adds defaults (persist&flush ProMeal).
     *
     * @param ProMeal $proMeal the pro-meal to use
     *
     * @return Collection a collection of MealOffers
     */
    private function getMealOffersOrAddthem(ProMeal $proMeal): Collection
    {
        $mealOffers = new ArrayCollection();
        $descTemplate = '<span class="dark">Social-Dining mit Mealmatch</span>
                                                          <p>Das Social-Dining Dinner #1</p>
                                                          <p>Hauptmenu</p>
                                                          <p>Getränk</p>
                                                          <p>Nachtisch</p>
                                                          <p>
                                                              Wir freuen uns auf Menschen die gemeinsam zusammen
                                                              finden und bei uns eine angenehmen Zeit beim Tischgespräch verbringen.
                                                          </p>';

        if ($proMeal->getMealOffers()->count() < 1) {
            $mealOffers->add(
                (new MealOffer())->setName('Mealmatch Menu #1')
                                 ->setDescription($descTemplate)
                                 ->setPrice(10.00)
            );
        } else {
            $mealOffers = $proMeal->getMealOffers();
        }
        $proMeal->setMealOffers($mealOffers);
        $this->get('doctrine.orm.entity_manager')->persist($proMeal);
        $this->get('doctrine.orm.entity_manager')->flush();

        return $mealOffers;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param Request $request
     * @param ProMeal $proMeal
     * @param string  $myArgument with a *description* of this argument, these may also
     *                            span multiple lines
     *
     * @return ArrayCollection
     */
    private function createProMealManagerEditViewData(Request $request, ProMeal $proMeal): ArrayCollection
    {
        $mealForm = $this->createMealForm($proMeal);
        $mealForm->handleRequest($request);

        $pageMealForm = $this->createPageMealForm($proMeal);
        $pageMealForm->handleRequest($request);

        $mealNotesForm = $this->createMealNotesForm($proMeal);
        $mealNotesForm->handleRequest($request);

        // Offers
        $mealOffers = $this->getMealOffersOrAddthem($proMeal);
        $offerForms = new ArrayCollection();
        foreach ($mealOffers as $mealOffer) {
            $offerForms->add($this->createMealOfferUpdateForm($proMeal, $mealOffer));
        }
        $offerFormViews = new ArrayCollection();
        /** @var Form $offerForm */
        foreach ($offerForms as $offerForm) {
            $offerForm->handleRequest($request);
            $offerFormViews->add($offerForm->createView());
        }

        // Events
        $mealEvents = $this->getMealEventsOrAddDefaults($proMeal);
        $eventForms = new ArrayCollection();
        foreach ($mealEvents as $mealEvent) {
            $eventForms->add($this->createMealEventUpdateForm($proMeal, $mealEvent));
        }
        $eventFormViews = new ArrayCollection();
        /** @var Form $eventForm */
        foreach ($eventForms as $eventForm) {
            $eventForm->handleRequest($request);
            $eventFormViews->add($eventForm->createView());
        }

        // Restaurant Profile of this ProMeal
        $restaurantProfile = $proMeal->getHost()->getRestaurantProfile();

        // Fill standard view Data from translations ...
        $viewTitle = $this->get('translator')->trans('promeal.manager.title', array(), 'Mealmatch');
        $subTitle = $this->get('translator')->trans('promeal.manager.subtitle', array(), 'Mealmatch');

        $viewData = array(
            'title' => $viewTitle,
            'subtitle' => $subTitle,
        );
        // Render view
        $renderViewData = new ArrayCollection(
            array(
                'proMeal' => $proMeal,
                'meal_form' => $mealForm->createView(),
                'page_meal_form' => $pageMealForm->createView(),
                'mealNotes_form' => $mealNotesForm->createView(),
                'selectedTab' => $this->getSelectedTab($request),
                'offerForms' => $offerFormViews->toArray(),
                'eventForms' => $eventFormViews->toArray(),
                'rProfile' => $restaurantProfile,
                'viewData' => $viewData,
            )
        );

        return $renderViewData;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param Request $request
     * @param ProMeal $proMeal
     * @param string  $myArgument with a *description* of this argument, these may also
     *                            span multiple lines
     *
     * @return ArrayCollection
     */
    private function createProMealManagerShowViewData(Request $request, ProMeal $proMeal): ArrayCollection
    {
        $mealForm = $this->createMealForm($proMeal);
        $mealForm->handleRequest($request);

        $mealNotesForm = $this->createMealNotesForm($proMeal);
        $mealNotesForm->handleRequest($request);

        // Offers
        $mealOffers = $this->getMealOffersOrAddthem($proMeal);
        $offerForms = new ArrayCollection();
        foreach ($mealOffers as $mealOffer) {
            $offerForms->add($this->createMealOfferUpdateForm($proMeal, $mealOffer));
        }
        $offerFormViews = new ArrayCollection();
        /** @var Form $offerForm */
        foreach ($offerForms as $offerForm) {
            $offerForm->handleRequest($request);
            $offerFormViews->add($offerForm->createView());
        }

        // Events
        $mealEvents = $this->getMealEventsOrAddDefaults($proMeal);
        $eventForms = new ArrayCollection();
        foreach ($mealEvents as $mealEvent) {
            $eventForms->add($this->createMealEventUpdateForm($proMeal, $mealEvent));
        }
        $eventFormViews = new ArrayCollection();
        /** @var Form $eventForm */
        foreach ($eventForms as $eventForm) {
            $eventForm->handleRequest($request);
            $eventFormViews->add($eventForm->createView());
        }

        // Restaurant Profile of this ProMeal
        $restaurantProfile = $proMeal->getHost()->getRestaurantProfile();

        // Render view
        $renderViewData = new ArrayCollection(
            array(
                'proMeal' => $proMeal,
                'meal_form' => $mealForm->createView(),
                'mealNotes_form' => $mealNotesForm->createView(),
                'selectedTab' => $this->getSelectedTab($request),
                'offerForms' => $offerFormViews->toArray(),
                'eventForms' => $eventFormViews->toArray(),
                'rProfile' => $restaurantProfile,
            )
        );

        return $renderViewData;
    }
}
