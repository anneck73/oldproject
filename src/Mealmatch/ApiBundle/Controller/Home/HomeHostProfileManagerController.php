<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Controller\Home;

use Doctrine\Common\Collections\ArrayCollection;
use Mealmatch\ApiBundle\Controller\ApiController;
use Mealmatch\ApiBundle\Entity\Restaurant\RestaurantAddress;
use Mealmatch\ApiBundle\Form\RestaurantProfile\EditCompanyType;
use Mealmatch\ApiBundle\Form\RestaurantProfile\EditDescriptionType;
use Mealmatch\ApiBundle\Form\RestaurantProfile\EditGeoAddressType;
use Mealmatch\ApiBundle\Form\RestaurantProfile\RestaurantImageType;
use Mealmatch\ApiBundle\Services\GeoAddressService;
use Mealmatch\ApiBundle\Services\RestaurantProfileManagerService;
use MMApiBundle\MealMatch\FlashTypes;
use MMUserBundle\Entity\MMRestaurantProfile;
use MMUserBundle\Entity\MMUser;
use MMUserBundle\Entity\MMUserPaymentProfile;
use MMUserBundle\Entity\MMUserProfile;
use MMUserBundle\Entity\MMUserSettings;
use MMUserBundle\Entity\RestaurantFile;
use MMUserBundle\Entity\RestaurantImage;
use MMUserBundle\Form\HomeHostPaymentUserNaturalType;
use MMUserBundle\Form\MMUserProfileTypePayment;
use MMUserBundle\Form\MMUserProfileTypePictureOnly;
use MMUserBundle\Form\RestaurantFileType;
use MMUserBundle\Form\RestaurantPaymentBankAccountType;
use MMUserBundle\Form\RestaurantProfileBasicType;
use MMUserBundle\Form\RestaurantProfileBusinessType;
use MMUserBundle\Form\RestaurantProfileGeoLocationStringType;
use MMUserBundle\Form\RestaurantProfileSettingsType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The HomeHost Profile Manager Controller manages the "Gastgeber Einstellungen" (mostly payment) for HomeMeal hosts.
 *
 * @Route("/u/homehostprofile/manager")
 * @Security("has_role('ROLE_HOME_USER')")
 */
class HomeHostProfileManagerController extends ApiController
{
    /**
     * Shows the main management interface to the user.
     *
     * @param Request $request
     * @Route("/show", name="homehost_profile_manager_show")
     * @Route("/", name="api_homehost_profile_manager", methods={"GET"})
     *
     * @return Response
     */
    public function showManagerAction(Request $request): Response
    {
        /** @var MMUser $user */
        $user = $this->getUser();

        /** @var MMUserProfile $userProfile */
        $userProfile = $user->getProfile();
        /** @var MMUserPaymentProfile $userPaymentProfile */
        $userPaymentProfile = $user->getPaymentProfile();

        // Default geo coordinates for Köln/Cologne
        // 50.93333, 6.95
        $geoDataArray = array(
            'lat' => 50.93333,
            'long' => 6.95,
        );

        $profilePictureForm = $this->createForm(MMUserProfileTypePictureOnly::class, $userProfile);

        // Render view
        $renderViewData = new ArrayCollection(array(
                'uProfile' => $userProfile,
                'pProfile' => $userPaymentProfile,
                'geoAddress' => $geoDataArray,
                'userPicForm' => $profilePictureForm->createView(),
            )
        );

        return $this->render('@WEBUI/HomeHostProfileManager/show.twig', $renderViewData->toArray());
    }

    /**
     * Shows the edit description form to the user.
     *
     * @param Request $request
     * @Route("/edit/description", name="homehost_profile_manager_edit_desc", methods={"GET"})
     *
     * @return Response
     */
    public function editDescriptionAction(Request $request): Response
    {
        /** @var MMUser $user */
        $user = $this->getUser();

        /** @var MMRestaurantProfile $restaurantProfile */
        $restaurantProfile = $user->getRestaurantProfile();

        $editDescriptionForm = $this->createForm(EditDescriptionType::class, $restaurantProfile,
            array(
                'action' => $this->generateUrl(
                    'restaurant_profile_manager_update_desc'
                ),
                'method' => 'POST',
            )
        );

        // Render view
        $renderViewData = new ArrayCollection(array(
                'rProfile' => $restaurantProfile,
                'editDescForm' => $editDescriptionForm->createView(),
            )
        );

        return $this->render('@WEBUI/RestaurantProfileManager/editDescription.twig', $renderViewData->toArray());
    }

    /**
     * Takes a POST request to update the restaurant profile description.
     *
     * @param Request $request
     * @Route("/edit/description", name="restaurant_profile_manager_update_desc", methods={"POST"})
     *
     * @return Response
     */
    public function updateDescriptionAction(Request $request): Response
    {
        /** @var MMUser $user */
        $user = $this->getUser();

        /** @var MMRestaurantProfile $restaurantProfile */
        $restaurantProfile = $user->getRestaurantProfile();
        /** @var RestaurantProfileManagerService $RPMService */
        $RPMService = $this->container->get('mm.restaurant.profilemanager.service');
        /** @var FormInterface $editDescriptionForm */
        $editDescriptionForm = $this->createForm(EditDescriptionType::class, $restaurantProfile);

        // Take values from POST Request and handle them (validate, etc)
        $editDescriptionForm->handleRequest($request);

        if ($editDescriptionForm->isSubmitted() && $editDescriptionForm->isValid()) {
            // Use service helper for backend functionality
            $RPMService->updateRestaurantProfile($restaurantProfile);
        }

        // @todo: handle errors from validation
        //        if (!$editDescriptionForm->isValid()) {
        //            $editDescriptionForm->getErrors();
        //        }

        // Render view
        $renderViewData = new ArrayCollection(array(
                'rProfile' => $restaurantProfile,
                'editDescForm' => $editDescriptionForm->createView(),
            )
        );

        return $this->render('@WEBUI/RestaurantProfileManager/editDescription.twig', $renderViewData->toArray());
    }

    /**
     * Shows the edit payment informations form to the user.
     *
     * @return Response
     * @Route("/edit/pics", name="homehost_profile_manager_edit_pics", methods={"GET"})
     */
    public function editPicturesAction(): Response
    {
        /** @var MMUser $user */
        $user = $this->getUser();

        /** @var MMRestaurantProfile $restaurantProfile */
        $restaurantProfile = $user->getRestaurantProfile();

        // Pictures of the restaurant ...
        $targetRoute = 'restaurant_picture_processing_add_picture';
        $uploadPictureForm = $this->createForm(RestaurantImageType::class, new RestaurantImage(),
            array(
                'action' => $this->generateUrl(
                    $targetRoute
                ),
                'method' => 'POST',
            )
        );
        // Render view
        $renderViewData = new ArrayCollection(array(
                'rProfile' => $restaurantProfile,
                'uploadPictureForm' => $uploadPictureForm->createView(),
            )
        );

        return $this->render('@WEBUI/RestaurantProfileManager/editPictures.twig', $renderViewData->toArray());
    }

    /**
     * Shows the edit payment informations form to the user.
     *
     * @return Response
     * @Route("/edit/payment", name="homehost_profile_manager_edit_payment", methods={"GET"})
     */
    public function editPaymentAction(): Response
    {
        /** @var MMUser $user */
        $user = $this->getUser();

        /** @var MMUserPaymentProfile $paymentProfile */
        $paymentProfile = $user->getPaymentProfile();

        $addNaturalUserForm = $this->createAddNaturalUserForm($user);
        $addBankAccountFrom = $this->createAddBankAccountForm($user);

        // Render view
        $renderViewData = new ArrayCollection(array(
                'pProfile' => $paymentProfile,
                'addNaturalUserForm' => $addNaturalUserForm->createView(),
                'addBankAccountForm' => $addBankAccountFrom->createView(),
            )
        );

        return $this->render('@WEBUI/HomeHostProfileManager/editPayment.twig', $renderViewData->toArray());
    }

    /**
     * Shows the edit homehost contact informations form to the user.
     *
     * @return Response
     * @Route("/edit/contact", name="homehost_profile_manager_edit_contact", methods={"GET"})
     */
    public function editCompanyAction()
    {
        /** @var MMUser $user */
        $user = $this->getUser();

        /** @var MMRestaurantProfile $restaurantProfile */
        $restaurantProfile = $user->getRestaurantProfile();

        /** @var $editCompanyForm */
        $editCompanyForm = $this->createForm(EditCompanyType::class, $restaurantProfile,
            array(
                'action' => $this->generateUrl(
                    'restaurant_profile_manager_update_company'
                ),
                'method' => 'POST',
            )
        );

        // Render view
        $renderViewData = new ArrayCollection(array(
                'rProfile' => $restaurantProfile,
                'companyForm' => $editCompanyForm->createView(),
            )
        );

        return $this->render('@WEBUI/RestaurantProfileManager/editCompany.twig', $renderViewData->toArray());
    }

    /**
     * Takes a POST request to update the restaurant profile company informations.
     *
     * @param Request $request
     * @Route("/edit/contact", name="homehost_profile_manager_update_contact", methods={"POST"})
     *
     * @return Response
     */
    public function updateContactAction(Request $request)
    {
        /** @var MMUser $user */
        $user = $this->getUser();

        /** @var MMRestaurantProfile $restaurantProfile */
        $restaurantProfile = $user->getRestaurantProfile();
        /** @var RestaurantProfileManagerService $RPMService */
        $RPMService = $this->container->get('mm.restaurant.profilemanager.service');
        /** @var FormInterface $editCompanyForm */
        $editCompanyForm = $this->createForm(EditCompanyType::class, $restaurantProfile);

        // Take values from POST Request and handle them (validate, etc)
        $editCompanyForm->handleRequest($request);

        if ($editCompanyForm->isSubmitted() && $editCompanyForm->isValid()) {
            // Use service helper for backend functionality
            $RPMService->updateRestaurantProfile($restaurantProfile);
        }

        // @todo: handle errors from validation
        //        if (!$editDescriptionForm->isValid()) {
        //            $editDescriptionForm->getErrors();
        //        }

        // Render view
        $renderViewData = new ArrayCollection(array(
                'rProfile' => $restaurantProfile,
                'companyForm' => $editCompanyForm->createView(),
            )
        );

        return $this->render('@WEBUI/RestaurantProfileManager/editCompany.twig', $renderViewData->toArray());
    }

    /**
     * Shows the edit geo location form to the user.
     * Creates the form to update the RestaurantAddressById.
     *
     * @return Response
     * @Route("/edit/geoAddress", name="homehost_profile_manager_edit_geo", methods={"GET"})
     */
    public function editGeoAddressAction()
    {
        /** @var MMUser $user */
        $user = $this->getUser();

        /** @var MMRestaurantProfile $restaurantProfile */
        $restaurantProfile = $user->getRestaurantProfile();

        // This is a safe measure for old restaurant profiles
        // RestaurantAddress is created in UserManager by default.
        if (!$restaurantProfile->hasAddress()) {
            $emptyRestaurantAddress = new RestaurantAddress();
            $emptyRestaurantAddress->setCoordinates(50.93333, 6.95);
            $restaurantProfile->addAddress($emptyRestaurantAddress);
            $this->get('doctrine.orm.entity_manager')->persist($restaurantProfile);
            $this->get('doctrine.orm.entity_manager')->flush();
        }

        $geoAddressForm = $this->createForm(EditGeoAddressType::class, $restaurantProfile,
            array(
                'action' => $this->generateUrl(
                    'restaurant_profile_manager_update_geo_address',
                    array('id' => $restaurantProfile->getAddress()->getId())
                ),
                'method' => 'POST',
            )
        );

        $geoDataArray = $this->getGeoCoordinates($restaurantProfile);

        // Render view
        $renderViewData = new ArrayCollection(array(
                'rProfile' => $restaurantProfile,
                'geoCoordinates' => $geoDataArray,
                'geoAddressForm' => $geoAddressForm->createView(),
                'rAddress' => $restaurantProfile->getAddress(),
            )
        );

        return $this->render('@WEBUI/RestaurantProfileManager/editGeoCoordinates.twig', $renderViewData->toArray());
    }

    /**
     * Takes a POST request to update the restaurant profile geo address (locationString) informations.
     *
     * @param Request           $request
     * @param RestaurantAddress $restaurantAddress
     *
     * @return Response
     * @Route("/edit/geoAddress/{id}", name="homehost_profile_manager_update_geo_address", methods={"POST"})
     */
    public function updateGeoAddressAction(Request $request, RestaurantAddress $restaurantAddress)
    {
        /** @var MMUser $user */
        $user = $this->getUser();

        /** @var RestaurantProfileManagerService $RPMService */
        $RPMService = $this->container->get('mm.restaurant.profilemanager.service');

        /** @var GeoAddressService $geoAddressService */
        $geoAddressService = $this->container->get('api.geo_address.service');

        /** @var MMRestaurantProfile $restaurantProfile */
        $restaurantProfile = $user->getRestaurantProfile();

        $editGeoAddressForm = $this->createForm(EditGeoAddressType::class, $restaurantProfile);
        $editGeoAddressForm->handleRequest($request);

        $serviceData = $geoAddressService->getAddressServiceData($restaurantAddress);

        if ($editGeoAddressForm->isValid() && $editGeoAddressForm->isSubmitted()) {
            // take the new locationString from restaurantProfile posted by EditGeoAddressType:Form
            // and set it into the RestaurantAddress to be updated!
            $restaurantAddress->setLocationString($restaurantProfile->getLocationString());
            // update the address entity with new locationString & geoEncode
            $serviceData = $geoAddressService->updateGeoAddress($restaurantAddress, false);
            if ($serviceData->isValid()) {
                $serviceData = $geoAddressService->updateGeoAddress($restaurantAddress, true);
                $this->addFlash(FlashTypes::$WARNING, 'Updated GeoAddress!');
                // Update the locationString in the RestaurantProfile to match the updated RestaurantAddress.
                //  $RPMService->updateRestaurantProfile($restaurantProfile);
            }
        }

        $geoEncodingErrs = $serviceData->getErrors();
        foreach ($geoEncodingErrs as $err) {
            $this->addFlash(FlashTypes::$WARNING, $err);
        }

        $formErrors = $this->getErrorMessages($editGeoAddressForm);
        foreach ($formErrors as $field => $error) {
            $this->addFlash(FlashTypes::$WARNING, $field.': '.implode(', ', $error));
        }

        return $this->redirectToRoute('restaurant_profile_manager_edit_geo');
    }

    /**
     * Takes the posted MMUserProfileTypePicturyOnly "Formdata" and processes it.
     * Depending on success or failure the user is redirected to the Manager+SelectedTab with or without an error.
     *
     * @Route("/updateProfilePicture", name="homehost_profile_manager_update_profile_picture", methods={"POST"})
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function updateProfilePictureAction(Request $request)
    {
        return $this->processUserProfileWithForm($request, MMUserProfileTypePictureOnly::class);
    }

    /**
     * Takes the posted RestaurantProfileBasicType "Formdata" and processes it.
     * Depending on success or failure the user is redirected to the Manager+SelectedTab with or without an error.
     *
     * @Route("/updateBasic", name="homehost_profile_manager_update_basic", methods={"POST"})
     *
     * @param Request $request
     *
     * @throws \ReflectionException
     *
     * @return RedirectResponse|Response
     */
    public function updateBasicAction(Request $request)
    {
        $this->get('logger')->addNotice('updateBasicAction');

        // Create the form using the entity description ...
        /** @var MMRestaurantProfile $restaurantProfile */
        $restaurantProfile = $this->getUser()->getRestaurantProfile();
        /** @var Form $restaurantProfileForm */
        $restaurantProfileForm = $this->createForm('MMUserBundle\Form\RestaurantProfileBasicType',
            $restaurantProfile);

        // Handle the data from the request
        $restaurantProfileForm->handleRequest($request);

        // Positive Case, all is valid ...
        if ($restaurantProfileForm->isSubmitted() && $restaurantProfileForm->isValid()) {
            $this->get('logger')->addError('updateBasicAction VALID!');
            $this->getDoctrine()->getManager()->persist($restaurantProfile);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute(
                'api_restaurant_profile_manager',
                array(
                    'selectedTab' => $this->getSelectedTab($request),
                )
            );
        }

        // Something went wrong ...
        $this->get('logger')->addError('updateBasicAction ERROR!');
        /** @var MMUserProfile $userProfile */
        $userProfile = $this->getUser()->getProfile();
        /** @var MMUserSettings $userSettings */
        $userSettings = $this->getUser()->getSettings();
        // User Profile Picture
        $formProfilePic = $this->createTabForm($userProfile, MMUserProfileTypePictureOnly::class, 1);
        // Basic Restaurantprofile basics ...
        // !!!!!!!
        $formTabOne = $restaurantProfileForm;
        // !!!!!!!!
        // Business stuff ...
        $formTabTwo = $this->createTabForm($restaurantProfile, RestaurantProfileBusinessType::class, 2);
        // The Restaurant Address ...
        $restaurantAddress = $this->getOrCreateNewRestaurantAddress($restaurantProfile);
        $formTabThree = $this->createRestaurantAddressUpdateForm($restaurantAddress);
        // Pictures of the restaurant ...
        $formTabFour = $this->createTabForm(new RestaurantImage(), RestaurantImageType::class, 4);
        // Legal documents
        $formLegalFiles = $this->createTabForm(new RestaurantFile(), RestaurantFileType::class, 2);
        // Form settings
        $formSeetings = $this->createTabForm($restaurantProfile, RestaurantProfileSettingsType::class, 5);

        // Render view
        $renderViewData = new ArrayCollection(array(
                'selectedTab' => $this->getSelectedTab($request),
                'formTabOne' => $formTabOne->createView(),
                'formTabTwo' => $formTabTwo->createView(),
                'formLegalFiles' => $formLegalFiles->createView(),
                'formTabThree' => $formTabThree->createView(),
                'formTabFour' => $formTabFour->createView(),
                'formProfilePic' => $formProfilePic->createView(),
                'formSettings' => $formSeetings->createView(),
                'rProfile' => $restaurantProfile,
                'uSettings' => $userSettings,
            )
        );

        return $this->render('@WEBUI/profiles/RestaurantProfile/manager.html.twig', $renderViewData->toArray());
    }

    /**
     * Takes the posted RestaurantProfileBusinessType "Formdata" and processes it.
     * Depending on success or failure the user is redirected to the Manager+SelectedTab with or without an error.
     *
     * @Route("/updateBusiness", name="homehost_profile_manager_update_business", methods={"POST"})
     *
     * @param Request $request
     *
     * @throws \ReflectionException
     *
     * @return RedirectResponse|Response
     */
    public function updateBusinessAction(Request $request)
    {
        $this->get('logger')->addNotice('updateBasicAction');

        // Create the form using the entity description ...
        /** @var MMRestaurantProfile $restaurantProfile */
        $restaurantProfile = $this->getUser()->getRestaurantProfile();

        /** @var Form $restaurantProfileForm */
        $restaurantProfileForm = $this->createForm('MMUserBundle\Form\RestaurantProfileBusinessType',
            $restaurantProfile);

        // Handle the data from the request
        $restaurantProfileForm->handleRequest($request);

        // Positive Case, all is valid ...
        if ($restaurantProfileForm->isSubmitted() && $restaurantProfileForm->isValid()) {
            $this->get('logger')->addError('updateBasicAction VALID!');
            $this->getDoctrine()->getManager()->persist($restaurantProfile);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute(
                'api_restaurant_profile_manager',
                array(
                    'selectedTab' => $this->getSelectedTab($request),
                )
            );
        }

        // Something went wrong ...
        $this->get('logger')->addError('updateBasicAction ERROR!');
        /** @var MMUserProfile $userProfile */
        $userProfile = $this->getUser()->getProfile();
        /** @var MMUserSettings $userSettings */
        $userSettings = $this->getUser()->getSettings();
        // User Profile Picture
        $formProfilePic = $this->createTabForm($userProfile, MMUserProfileTypePictureOnly::class, 1);
        // Basic Restaurantprofile basics ...
        $formTabOne = $this->createTabForm($restaurantProfile, RestaurantProfileBasicType::class, 1);
        // Business stuff ...
        // !!!!!!!
        $formTabTwo = $restaurantProfile;
        // !!!!!!!
        // The Restaurant Address ...
        $restaurantAddress = $this->getOrCreateNewRestaurantAddress($restaurantProfile);
        $formTabThree = $this->createRestaurantAddressUpdateForm($restaurantAddress);
        // Pictures of the restaurant ...
        $formTabFour = $this->createTabForm(new RestaurantImage(), RestaurantImageType::class, 4);
        // Legal documents
        $formLegalFiles = $this->createTabForm(new RestaurantFile(), RestaurantFileType::class, 2);
        // Form settings
        $formSeetings = $this->createTabForm($restaurantProfile, RestaurantProfileSettingsType::class, 5);

        // Render view
        $renderViewData = new ArrayCollection(array(
                'selectedTab' => $this->getSelectedTab($request),
                'formTabOne' => $formTabOne->createView(),
                'formTabTwo' => $formTabTwo->createView(),
                'formLegalFiles' => $formLegalFiles->createView(),
                'formTabThree' => $formTabThree->createView(),
                'formTabFour' => $formTabFour->createView(),
                'formProfilePic' => $formProfilePic->createView(),
                'formSettings' => $formSeetings->createView(),
                'rProfile' => $restaurantProfile,
                'uSettings' => $userSettings,
            )
        );

        return $this->render('@WEBUI/profiles/RestaurantProfile/manager.html.twig', $renderViewData->toArray());
        // return $this->processRestaurantProfileWithForm($request, RestaurantProfileBusinessType::class);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     * @todo: Update PHPDoc!
     *
     * @Route("/updateSettings", name="homehost_profile_manager_update_settings", methods={"POST"})
     */
    public function updateSettingsAction(Request $request)
    {
        return $this->processRestaurantProfileWithForm($request, RestaurantProfileSettingsType::class);
    }

    /**
     * @Route("/updateHomeHostPayment", name="homehost_profile_manager_update_payment", methods={"POST"})
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function updatePaymentAction(Request $request)
    {
        return $this->processUserPaymentProfileWithForm($request, MMUserProfileTypePayment::class);
    }

    /**
     * Updates RestaurantAddress and returns to api_restaurant_profile_manager_update.
     * Set's target RestaurantAddress according to /{id}/ in request.
     *
     * @Route("/{id}/updateAddress", name="homehost_profile_manager_update_address", methods={"POST"})
     *
     * @param Request           $request
     * @param RestaurantAddress $restaurantAddress
     *
     * @return RedirectResponse
     */
    public function updateAddressAction(Request $request, RestaurantAddress $restaurantAddress)
    {
        if ($this->getUser() !== $restaurantAddress->getCreatedBy()) {
            $this->createAccessDeniedException('You are not the creator of '.$restaurantAddress->getId());
        }

        $updateForm = $this->createForm(RestaurantProfileGeoLocationStringType::class, $restaurantAddress);
        $updateForm->handleRequest($request);

        if ($updateForm->isSubmitted() && $updateForm->isValid()) {
            $serviceData = $this->get('api.geo_address.service')->updateGeoAddress($restaurantAddress);
            if ($serviceData->isValid()) {
                $this->get('session')->set('serviceData/GeoAddress', $serviceData);
            }
        }

        $errors = $serviceData->getErrors();
        if ($errors->count() > 0) {
            foreach ($errors as $error) {
                $this->addFlash(FlashTypes::$DANGER, 'GeoEncoding failed: '.$error);
            }
        }

        return $this->redirectToRoute(
            'api_restaurant_profile_manager'
        );
    }

    /**
     * Add's a new RestaurantFile to the RestaurantProfile and returns to restaurant profile manager tab 2.
     *
     * @Route("/addFile", name="homehost_profile_manager_add_file", methods={"POST"})
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function addRestaurantFile(Request $request)
    {
        // The RestaurantFile to contain the new file.
        $file = new RestaurantFile();

        /** @var MMRestaurantProfile $restaurantProfile */
        $restaurantProfile = $this->getUser()->getRestaurantProfile();

        // Maximum 2 Files
        if (2 === $restaurantProfile->getLegalFiles()->count()) {
            $this->addFlash('warning', 'Es sind max. 2 Dateien pro Restaurant möglich!');

            return $this->redirectToRoute('api_restaurant_profile_manager', array('selectedTab' => 2));
        }

        $form = $this->createForm(RestaurantFileType::class, $file);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $restaurantProfile->addLegalFile($file);
            $em = $this->getDoctrine()->getManager();
            $em->persist($file);
            $em->persist($restaurantProfile);
            $em->flush();
        }

        return $this->redirectToRoute('api_restaurant_profile_manager', array('selectedTab' => 2));
    }

    /**
     * Removes an existing file.
     *
     * @Route("/{id}/deleteFile", name="homehost_profile_manager_delete_file", methods={"POST"})
     *
     * @param Request        $request
     * @param RestaurantFile $restaurantFile
     *
     * @return RedirectResponse
     */
    public function deleteRestaurantFile(Request $request, RestaurantFile $restaurantFile)
    {
        if ($this->getUser() !== $restaurantFile->getCreatedBy()) {
            $this->createAccessDeniedException('You are not the creator of '.$restaurantFile->getId());
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($restaurantFile);
        $entityManager->flush();

        return $this->redirectToRoute('api_restaurant_profile_manager', array('selectedTab' => 2));
    }

    /**
     * Removes an existing picture.
     *
     * @Route("/{id}/deletePicture", name="homehost_profile_manager_delete_picture", methods={"GET"})
     *
     * @param Request         $request
     * @param RestaurantImage $restaurantImage
     *
     * @return RedirectResponse
     */
    public function deleteRestaurantPicture(Request $request, RestaurantImage $restaurantImage)
    {
        if ($this->getUser() !== $restaurantImage->getCreatedBy()) {
            $this->createAccessDeniedException('You are not the creator of '.$restaurantImage->getId());
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($restaurantImage);
        $entityManager->flush();

        return $this->redirectToRoute('api_restaurant_profile_manager', array('selectedTab' => 4));
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Private Methods -------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Uses the Request to process PaymentProfile.
     *
     * @param Request $request
     * @param string  $formTypeClass
     *
     * @return RedirectResponse
     */
    private function processUserPaymentProfileWithForm(Request $request, string $formTypeClass): RedirectResponse
    {
        /** @var @var MMuser $user */
        $user = $this->getUser();

        $userPaymentProfile = $user->getPaymentProfile();
        $userPaymentProfileForm = $this->createForm($formTypeClass, $userPaymentProfile);
        $userPaymentProfileForm->handleRequest($request);

        if ($userPaymentProfileForm->isSubmitted() && $userPaymentProfileForm->isValid()) {
            $this->getDoctrine()->getManager()->persist($userPaymentProfile);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute('api_restaurant_profile_manager',
            array(
                'selectedTab' => $this->getSelectedTab($request),
            )
        );
    }

    /**
     * Uses the Request to process the MMUserProfile with the specified FormTypeClass.
     * Enables the use of different FormTypeClasses on MMUserProfile.
     *
     * @param Request $request       the request to process
     * @param string  $formTypeClass the FormType CLASS to use
     *
     * @return RedirectResponse redirects the use to the ProMealManager-SelectedTab
     */
    private function processUserProfileWithForm(Request $request, string $formTypeClass): RedirectResponse
    {
        /** @var MMUser $user */
        $user = $this->getUser();
        /** @var MMUserProfile $userProfile */
        $userProfile = $this->getUser()->getProfile();
        $userProfileForm = $this->createForm($formTypeClass, $userProfile);
        $userProfileForm->handleRequest($request);

        if ($userProfileForm->isSubmitted() && $userProfileForm->isValid()) {
            // $file stores the uploaded PDF file
            /** @var UploadedFile $file */
            $file = $userProfile->getImageFile();

            if (null !== $file) {
                $profileImageName = 'image/u'.$user->getHash();
                $fileName = $this->get('mm_user.image_uploader')->upload($file, $profileImageName);
                $userProfile->setImageName($fileName);
            }

            $this->getDoctrine()->getManager()->persist($userProfile);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute(
            'api_restaurant_profile_manager',
            array(
                'selectedTab' => 1,
            )
        );
    }

    private function getErrorMessages(Form $form)
    {
        $errors = array();

        foreach ($form->getErrors() as $key => $error) {
            if ($form->isRoot()) {
                $errors['#'][] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }

        /** @var FormInterface $child */
        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }

        return $errors;
    }

    private function createAddBankAccountForm(MMUser $user)
    {
        // Re-Using RestaurantPaymentBankAccount for now, it should work ;)
        $formTypeClass = RestaurantPaymentBankAccountType::class;
        $targetRoute = 'homehost_payment_processing_add_bankaccount';

        // create and return ...
        return $this->createForm(
            $formTypeClass,
            array(),
            array(
                'action' => $this->generateUrl(
                    $targetRoute
                ),
                'method' => 'POST',
            )
        );
    }

    private function createAddNaturalUserForm(MMUser $user): FormInterface
    {
        /** @var HomeHostPaymentUserNaturalType $formTypeClass */
        $formTypeClass = HomeHostPaymentUserNaturalType::class;

        // Using values from profiles to pre-fill the form ...
        $formData['Name'] = $user->getProfile()->getLastName();

        // The target to "POST" values to
        $targetURL = $this->generateUrl(
            'homehost_payment_processing_add_natural_user'
        );
        // create and return ...
        return $this->createForm(
            $formTypeClass,
            $formData,
            array(
                'action' => $targetURL,
                'method' => 'POST',
            )
        );
    }
}
