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
use Mealmatch\ApiBundle\Entity\Meal\HomeMeal;
use Mealmatch\ApiBundle\Entity\Meal\MealAddress;
use Mealmatch\ApiBundle\Entity\Meal\MealEvent;
use Mealmatch\ApiBundle\Entity\Meal\MealPart;
use Mealmatch\ApiBundle\Form\Meal\HomeMealPageType;
use Mealmatch\ApiBundle\Form\Meal\HomeMealTypeTabOne;
use Mealmatch\ApiBundle\Form\Meal\MealAddressType;
use Mealmatch\ApiBundle\Form\Meal\MealEventType;
use Mealmatch\ApiBundle\Model\GeoAddressServiceData;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

/**
 * The HomeMealManagerController is resposible for guiding the Host through the process of creating
 * HomeMeals.
 *
 * @Route("api/homemeal/manager")
 * @Security("has_role('ROLE_USER')")
 */
class HomeMealManagerController extends ApiController
{
    /**
     * Shows the main management interface to the user.
     *
     * @param Request  $request
     * @param HomeMeal $homeMeal
     * @Route("/{id}/edit", name="api_homemeal_manager_edit")
     * @Method("GET")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, HomeMeal $homeMeal)
    {
        // Verify that the meal exists ...
        if (null === $homeMeal) {
            $id = $request->get('id');
            $msg = $this->get('translator')->trans('meal.not_found', array('%mealid%' => $id), 'Mealmatch');
            $this->addFlash('info', $msg);

            return $this->redirectToRoute('api_homemeal_index');
        }

        // Verify that current user is OWNER
        if ($this->getUser() !== $homeMeal->getCreatedBy()) {
            $this->createAccessDeniedException('You are not the creator of '.$homeMeal->getId());
        }

        // Only edit if STATUS IS NOT RUNNING
        if (ApiConstants::MEAL_STATUS_RUNNING === $homeMeal->getStatus()) {
            $msg = $this->get('translator')->trans('meal.edit.error.is_running', array(), 'Mealmatch');
            $this->addFlash('info', $msg);

            return $this->redirectToRoute('api_homemeal_index');
        }

        $renderViewData = $this->createHomeMealCreateViewData($request, $homeMeal);

        return $this->render('@WEBUI/HomeMeal/managerEdit.html.twig', $renderViewData);
    }

    /**
     * Shows the main management interface to the user.
     *
     * @param Request  $request
     * @param HomeMeal $homeMeal
     * @Route("/{id}/show", name="api_homemeal_manager_show")
     * @Method("GET")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showManager(Request $request, HomeMeal $homeMeal = null)
    {
        // Verify that the meal exists ...
        if (null === $homeMeal) {
            $id = $request->get('id');
            $msg = $this->get('translator')->trans('meal.not_found', array('%mealid%' => $id), 'Mealmatch');
            $this->addFlash('info', $msg);

            return $this->redirectToRoute('api_meal_index');
        }
        // @todo: maybe this needs a custom method!?
        $renderViewData = $this->createHomeMealShowViewData($request, $homeMeal);

        return $this->render('@WEBUI/HomeMeal/managerShow.html.twig', $renderViewData);
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param Request  $request
     * @param HomeMeal $homeMeal
     * @Route("/{id}/updateMeal", name="api_homemeal_manager_update_meal")
     * @Method("POST")
     */
    public function updateMealAction(Request $request, HomeMeal $homeMeal)
    {
        if ($this->getUser() !== $homeMeal->getCreatedBy()) {
            $this->createAccessDeniedException('You are not the creator of '.$homeMeal->getId());
        }

        $mealForm = $this->createForm(
            'Mealmatch\ApiBundle\Form\Meal\HomeMealTypeTabOne',
            $homeMeal
        );
        $mealForm->handleRequest($request);

        if ($mealForm->isSubmitted() && $mealForm->isValid()) {
            $this->getDoctrine()->getManager()->persist($homeMeal);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute(
                'api_homemeal_manager_edit',
                array(
                    'id' => $homeMeal->getId(),
                    'selectedTab' => '1',
                )
            );
        }

//        die('Doh!'); @todo Sollte ein Logentry sein. Kann nur isValid oder not valid sein
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param Request  $request
     * @param HomeMeal $homeMeal
     * @Route("/{id}/updatePageMeal", name="api_homemeal_manager_update_page_meal")
     * @Method("POST")
     */
    public function updatePageMealAction(Request $request, HomeMeal $homeMeal)
    {
        if ($this->getUser() !== $homeMeal->getCreatedBy()) {
            $this->createAccessDeniedException('You are not the creator of '.$homeMeal->getId());
        }

        $mealForm = $this->createForm(
            'Mealmatch\ApiBundle\Form\Meal\HomeMealPageType',
            $homeMeal
        );
        $mealForm->handleRequest($request);

        if ($mealForm->isSubmitted() && $mealForm->isValid()) {
            $this->getDoctrine()->getManager()->persist($homeMeal);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute(
                'api_homemeal_manager_edit',
                array(
                    'id' => $homeMeal->getId(),
                    'selectedTab' => '1',
                )
            );
        }
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param Request  $request
     * @param HomeMeal $homeMeal
     * @Route("/{id}/updateMealOptions", name="api_homemeal_manager_update_meal_options")
     * @Method("POST")
     */
    public function updateMealOptionsAction(Request $request, HomeMeal $homeMeal)
    {
        if ($this->getUser() !== $homeMeal->getCreatedBy()) {
            $this->createAccessDeniedException('You are not the creator of '.$homeMeal->getId());
        }

        $mealForm = $this->createForm(
            'Mealmatch\ApiBundle\Form\Meal\HomeMealTypeTabTwo',
            $homeMeal
        );
        $mealForm->handleRequest($request);

        if ($mealForm->isSubmitted() && $mealForm->isValid()) {
            $this->getDoctrine()->getManager()->persist($homeMeal);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute(
            'api_homemeal_manager_edit',
            array(
                'id' => $homeMeal->getId(),
                'selectedTab' => '2',
            )
        );
    }

    /**
     * Adds a new MealEvent into a HomeMeal.
     *
     * @Route("/{id}/addEvent", name="api_homemeal_manager_addEvent")
     * @Method({"GET","POST"})
     */
    public function addEventAction(Request $request, HomeMeal $homeMeal)
    {
        if ($this->getUser() !== $homeMeal->getCreatedBy()) {
            $this->createAccessDeniedException('You are not the creator of '.$homeMeal->getId());
        }

        $newEvent = new MealEvent();
        $newEvent->setStartDateTime(new \DateTime('now'));
        $newEvent->setEndDateTime(new \DateTime('+6 Hours'));

        $homeMeal->addMealEvent($newEvent);

        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute(
            'api_homemeal_manager_edit',
            array(
                'id' => $homeMeal->getId(),
                'selectedTab' => 4,
            )
        );
    }

    /**
     * @todo: MealParts are not used yet ...
     * Adds a new MealPart into a HomeMeal.
     *
     * @Route("/{id}/addPart", name="api_homemeal_manager_addPart")
     * @Method({"GET","POST"})
     */
    public function addPartAction(Request $request, HomeMeal $homeMeal)
    {
        if ($this->getUser() !== $homeMeal->getCreatedBy()) {
            $this->createAccessDeniedException('You are not the creator of '.$homeMeal->getId());
        }

        $newPart = new MealPart();
        $newPart->setName('Bezeichnung');
        $newPart->setDescription('Beschreibung');

        $homeMeal->addMealPart($newPart);

        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute(
            'api_homemeal_manager_edit',
            array(
                'id' => $homeMeal->getId(),
                'selectedTab' => 4,
            )
        );
    }

    /**
     * Updates MealAddress and returns to api_homemeal_manager_edit.
     * Set's target HomeMeal according to request->get('homeMealID').
     * Set's target selectedTab parameter!
     *
     * @Route("/{id}/updateAddress", name="api_homemeal_manager_address_update")
     * @Method({"POST"})
     */
    public function updateAddressAction(Request $request, MealAddress $mealAddress = null)
    {
        if (null === $mealAddress) {
            $mealAddress = new MealAddress();
        }
        if ($this->getUser() !== $mealAddress->getCreatedBy()) {
            $this->createAccessDeniedException('You are not the creator of '.$mealAddress->getId());
        }

        $updateForm = $this->createForm(MealAddressType::class, $mealAddress);
        $updateForm->handleRequest($request);

        if ($updateForm->isSubmitted() && $updateForm->isValid()) {
            $serviceData = $this->get('api.geo_address.service')->updateGeoAddress($mealAddress);
            $this->get('session')->set('serviceData/GeoAddress', $serviceData);
        }

        $homeMealID = $request->get('homeMealID');

        return $this->redirectToRoute(
            'api_homemeal_manager_edit',
            array(
                'id' => $homeMealID,
                'selectedTab' => '3',
            )
        );
    }

    private function createMealEventUpdateForm(HomeMeal $homeMeal, MealEvent $mealEvent): Form
    {
        if ($this->getUser() !== $homeMeal->getCreatedBy()) {
            $this->createAccessDeniedException('You are not the creator of '.$homeMeal->getId());
        }

        try {
            return $this->createForm(
                MealEventType::class,
                $mealEvent,
                array(
                    'action' => $this->generateUrl(
                        'api_mealevent_update',
                        array(
                            'id' => $mealEvent->getId(),
                            'mealID' => $homeMeal->getId(),
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

    private function createMealForm(HomeMeal $homeMeal, $mealForm, $targetRoute): Form
    {
        $mealForm = $this->createForm(
            $mealForm,
            $homeMeal,
            array(
                'action' => $this->generateUrl(
                    $targetRoute,
                    array('id' => $homeMeal->getId())
                ),
                'method' => 'POST',
                'currency' => $homeMeal->getSharedCostCurrency(),
            )
        );

        return $mealForm;
    }

    private function createMealOptionsForm(HomeMeal $homeMeal): Form
    {
        $mealForm = $this->createForm(
            'Mealmatch\ApiBundle\Form\Meal\HomeMealTypeTabTwo',
            $homeMeal,
            array(
                'action' => $this->generateUrl(
                    'api_homemeal_manager_update_meal_options',
                    array('id' => $homeMeal->getId())
                ),
                'method' => 'POST',
                'currency' => $homeMeal->getSharedCostCurrency(),
            )
        );

        return $mealForm;
    }

    private function getMealEventsOrAddDefaults(HomeMeal $homeMeal): Collection
    {
        $mealEvents = $homeMeal->getMealEvents();
        $defaultStartDateTime = new \DateTime('now');
        if ($homeMeal->getMealEvents()->count() < 1) {
            /** @var MealEvent $mealEvent */
            $mealEvent = (new MealEvent())
                ->setStartDateTime($defaultStartDateTime);
            $mealEvents->add($mealEvent);
        } else {
            $mealEvents = $homeMeal->getMealEvents();
        }
        $homeMeal->setMealEvents($mealEvents);
        $this->get('doctrine.orm.entity_manager')->persist($homeMeal);
        $this->get('doctrine.orm.entity_manager')->flush();

        return $mealEvents;
    }

    private function createMealAddressUpdateForm(HomeMeal $homeMeal): Form
    {
        // @todo: remove possible null pointer on meal address
        // @todo: remove die()!!!!
        try {
            return $this->createForm(
                MealAddressType::class,
                $homeMeal->getMealAddress(),
                array(
                    'action' => $this->generateUrl(
                        'api_homemeal_manager_address_update',
                        array(
                            'id' => $homeMeal->getMealAddress()->getId(),
                            'homeMealID' => $homeMeal->getId(),
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

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param Request  $request
     * @param HomeMeal $homeMeal
     *
     * @return array
     */
    private function createHomeMealCreateViewData(Request $request, HomeMeal $homeMeal): array
    {
        // Meal basics new UI
        $mealPageForm = $this->createMealForm($homeMeal, HomeMealPageType::class, 'api_homemeal_manager_update_page_meal');

        // Meal basics in TAB 1
        $mealForm = $this->createMealForm($homeMeal, HomeMealTypeTabOne::class, 'api_homemeal_manager_update_meal');

        // Meal other in TAB 2
        $mealOptionsForm = $this->createMealOptionsForm($homeMeal);

        // Addresses
        $mealAddressForm = $this->createMealAddressUpdateForm($homeMeal);

        if ($this->get('session')->has('serviceData/GeoAddress')) {
            /** @var GeoAddressServiceData $geoData */
            $geoData = $this->get('session')->get('serviceData/GeoAddress');
            if (!$geoData->isValid()) {
                $formError = new FormError($geoData->getErrors()->last());
                $mealAddressForm->get('locationString')->addError($formError);
            }
        }

        // Events
        $mealEvents = $this->getMealEventsOrAddDefaults($homeMeal);
        $eventForms = new ArrayCollection();
        foreach ($mealEvents as $mealEvent) {
            $eventForms->add($this->createMealEventUpdateForm($homeMeal, $mealEvent));
        }
        $eventFormViews = new ArrayCollection();
        /** @var Form $eventForm */
        foreach ($eventForms as $eventForm) {
            $eventForm->handleRequest($request);
            $eventFormViews->add($eventForm->createView());
        }
        // Fill standard view Data from translations ...
        $viewTitle = $this->get('translator')->trans('promeal.manager.title', array(), 'Mealmatch');
        $subTitle = $this->get('translator')->trans('promeal.manager.subtitle', array(), 'Mealmatch');

        $viewData = array(
            'title' => $viewTitle,
            'subtitle' => $subTitle,
        );

        // Render view
        $renderViewData = array(
            'homeMeal' => $homeMeal,
            'meal_form' => $mealForm->createView(),
            'meal_page_form' => $mealPageForm->createView(),
            'meal_option_form' => $mealOptionsForm->createView(),
            'address_form' => $mealAddressForm->createView(),
            'selectedTab' => $this->getSelectedTab($request),
            'eventForms' => $eventFormViews->toArray(),
            'viewData' => $viewData,
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
     * @param Request  $request
     * @param HomeMeal $homeMeal
     * @param string   $myArgument with a *description* of this argument, these may also
     *                             span multiple lines
     *
     * @return array
     */
    private function createHomeMealShowViewData(Request $request, HomeMeal $homeMeal): array
    {
        // Render view
        $renderViewData = array(
            'homeMeal' => $homeMeal,
            'selectedTab' => $this->getSelectedTab($request),
        );

        return $renderViewData;
    }
}
