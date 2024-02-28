<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Controller\Restaurant;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Mealmatch\ApiBundle\Controller\ApiController;
use Mealmatch\ApiBundle\Entity\EntityData;
use Mealmatch\ApiBundle\Entity\Restaurant\RestaurantAddress;
use Mealmatch\ApiBundle\Form\RestaurantProfile\EditCompanyType;
use Mealmatch\ApiBundle\Form\RestaurantProfile\EditDescriptionType;
use Mealmatch\ApiBundle\Form\RestaurantProfile\EditGeoAddressType;
use Mealmatch\ApiBundle\Form\RestaurantProfile\RestaurantImageType;
use Mealmatch\ApiBundle\Services\GeoAddressService;
use Mealmatch\ApiBundle\Services\RestaurantProfileManagerService;
use Mealmatch\ApiBundle\Services\RestaurantService;
use MMApiBundle\MealMatch\FlashTypes;
use MMUserBundle\Entity\MMRestaurantProfile;
use MMUserBundle\Entity\MMUser;
use MMUserBundle\Entity\MMUserPaymentProfile;
use MMUserBundle\Entity\MMUserProfile;
use MMUserBundle\Entity\MMUserSettings;
use MMUserBundle\Entity\RestaurantFile;
use MMUserBundle\Entity\RestaurantImage;
use MMUserBundle\Form\MMUserProfileTypePayment;
use MMUserBundle\Form\MMUserProfileTypePictureOnly;
use MMUserBundle\Form\RestaurantFileType;
use MMUserBundle\Form\RestaurantPaymentBankAccountType;
use MMUserBundle\Form\RestaurantPaymentUserLegalType;
use MMUserBundle\Form\RestaurantProfileBasicType;
use MMUserBundle\Form\RestaurantProfileBusinessType;
use MMUserBundle\Form\RestaurantProfileGeoLocationStringType;
use MMUserBundle\Form\RestaurantProfileSettingsType;
use MMUserBundle\Form\RestaurantProfileSinglePageType;
use ReflectionClass;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The RestaurantProfileManager "manages" the RestaurantProfile editing.
 *
 * @Route("/u/restaurantprofile/manager")
 * @Security("has_role('ROLE_RESTAURANT_USER')")
 */
class RestaurantProfileManagerController extends ApiController
{
    /**
     * Shows the main management interface to the user.
     *
     * @param Request $request
     * @Route("/show", name="restaurant_profile_manager_show")
     * @Route("/", name="api_restaurant_profile_manager", methods={"GET"})
     *
     * @return Response
     */
    public function showManagerAction(Request $request)
    {
        /** @var RestaurantProfileManagerService $helperService */
        $helperService = $this->get('mm.restaurant.profilemanager.service');

        /** @var MMUser $user */
        $user = $this->getUser();

        // This will enable all profiles required for the RestaurantProfileManager to work.
        // $helperService->autofixUserProfiles($user);

        /** @var MMUserProfile $userProfile */
        $userProfile = $user->getProfile();
        /** @var MMUserPaymentProfile $userPaymentProfile */
        $userPaymentProfile = $user->getPaymentProfile();
        /** @var MMRestaurantProfile $restaurantProfile */
        $restaurantProfile = $user->getRestaurantProfile();
        /** @var RestaurantAddress $restaurantAddress */
        $restaurantAddress = $restaurantProfile->getAddress();

        // Default geo coordinates for Köln/Cologne
        // 50.93333, 6.95
        $geoDataArray = array(
            'lat' => 50.93333,
            'long' => 6.95,
        );
        // Note: First time login of a restaurant user (no address yet)
        if (null !== $restaurantAddress) {
            // This works only if geo data resolved successfully!
            list($coords['lat'], $coords['long']) = $restaurantAddress->getCoordinates();
            // Check if we have coordinates in restaurantAddress:
            if ($coords['lat'] > 0 && $coords['long'] > 0) {
                // OK! use them!
                $geoDataArray = $coords;
            }
        }

        $profilePictureForm = $this->createForm(MMUserProfileTypePictureOnly::class, $userProfile);

        // Render view
        $renderViewData = new ArrayCollection(array(
                'rProfile' => $restaurantProfile,
                'pProfile' => $userPaymentProfile,
                'geoAddress' => $geoDataArray,
                'userPicForm' => $profilePictureForm->createView(),
            )
        );

        return $this->render('@WEBUI/RestaurantProfileManager/show.twig', $renderViewData->toArray());
    }

    /**
     * Shows the edit description form to the user.
     *
     * @param Request $request
     * @Route("/edit/description", name="restaurant_profile_manager_edit_desc", methods={"GET"})
     *
     * @return Response
     */
    public function editDescriptionAction(Request $request)
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
    public function updateDescriptionAction(Request $request)
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
     * @Route("/edit/pics", name="restaurant_profile_manager_edit_pics", methods={"GET"})
     */
    public function editPicturesAction()
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
     * @Route("/edit/payment", name="restaurant_profile_manager_edit_payment", methods={"GET"})
     */
    public function editPaymentAction(): Response
    {
        /** @var MMUser $user */
        $user = $this->getUser();

        /** @var MMRestaurantProfile $restaurantProfile */
        $restaurantProfile = $user->getRestaurantProfile();

        /** @var MMUserPaymentProfile $paymentProfile */
        $paymentProfile = $user->getPaymentProfile();

        $addLegalUserForm = $this->createAddLegalUserForm($user);
        $addBankAccountFrom = $this->createAddBankAccountForm($user);

        // Render view
        $renderViewData = new ArrayCollection(array(
                'rProfile' => $restaurantProfile,
                'pProfile' => $paymentProfile,
                'addLegalUserForm' => $addLegalUserForm->createView(),
                'addBankAccountForm' => $addBankAccountFrom->createView(),
            )
        );

        return $this->render('@WEBUI/RestaurantProfileManager/editPayment.twig', $renderViewData->toArray());
    }

    /**
     * Shows the edit company informations form to the user.
     *
     * @return Response
     * @Route("/edit/company", name="restaurant_profile_manager_edit_company", methods={"GET"})
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
     * @Route("/edit/company", name="restaurant_profile_manager_update_company", methods={"POST"})
     *
     * @return Response
     */
    public function updateCompanyAction(Request $request)
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
     * @Route("/edit/geoAddress", name="restaurant_profile_manager_edit_geo", methods={"GET"})
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
     * @Route("/edit/geoAddress/{id}", name="restaurant_profile_manager_update_geo_address", methods={"POST"})
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
     * @Route("/updateProfilePicture", name="restaurant_profile_manager_update_profile_picture", methods={"POST"})
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
     * @Route("/updateBasic", name="restaurant_profile_manager_update_basic", methods={"POST"})
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
     * @Route("/updateBusiness", name="restaurant_profile_manager_update_business", methods={"POST"})
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
     * @Route("/updateSettings", name="restaurant_profile_manager_update_settings", methods={"POST"})
     */
    public function updateSettingsAction(Request $request)
    {
        return $this->processRestaurantProfileWithForm($request, RestaurantProfileSettingsType::class);
    }

    /**
     * @Route("/updateRestaurantPayment", name="restaurant_profile_manager_update_payment", methods={"POST"})
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
     * @Route("/{id}/updateAddress", name="restaurant_profile_manager_update_address", methods={"POST"})
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
     * @Route("/addFile", name="restaurant_profile_manager_add_file", methods={"POST"})
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
     * @Route("/{id}/deleteFile", name="restaurant_profile_manager_delete_file", methods={"POST"})
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
     * @Route("/{id}/deletePicture", name="restaurant_profile_manager_delete_picture", methods={"GET"})
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

    /**
     * Takes the posted SinglePageFormType "Formdata" and processes it.
     *
     * @Route("/updateSinglePage", name="restaurant_profile_manager_single_page_update", methods={"POST"})
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return RedirectResponse
     */
    public function updateSinglePageAction(Request $request)
    {
        $this->get('logger')->addNotice('updateSinglePageAction');

        return $this->processSinglePageWithForm($request, RestaurantProfileSinglePageType::class);
    }

    /**
     * @param MMRestaurantProfile $restaurantProfile
     *
     * @return array
     */
    protected function getGeoCoordinates(MMRestaurantProfile $restaurantProfile): array
    {
        /** @var RestaurantAddress $restaurantAddress */
        $restaurantAddress = $restaurantProfile->getAddress();

        // Default geo coordinates for Köln/Cologne 50.93333, 6.95
        $geoDataArray = array('lat' => 50.93333, 'long' => 6.95);

        // This works only if geo data resolved successfully!
        list($coords['lat'], $coords['long']) = $restaurantAddress->getCoordinates();
        // Check if we have coordinates in restaurantAddress:
        if ($coords['lat'] > 0 && $coords['long'] > 0) {
            // OK! use them!
            $geoDataArray = $coords;
        }

        return $geoDataArray;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Private Methods -------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Creates the Form for the Restaurant-Address-Update.
     *
     * @param RestaurantAddress $restaurantAddress the restaurant address entity to build the form with
     *
     * @return Form the form to update the RestaurantAddress
     */
    private function createRestaurantAddressUpdateForm(RestaurantAddress $restaurantAddress): Form
    {
        try {
            return $this->createForm(
                RestaurantProfileGeoLocationStringType::class,
                $restaurantAddress,
                array(
                    'action' => $this->generateUrl(
                        'restaurant_profile_manager_update_address',
                        array(
                            'id' => $restaurantAddress->getId(),
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
     * Creates the FORM including a target route depending on the FormType.
     *
     * @param EntityData $entity_data   the EntityData to include in the form
     * @param string     $formTypeClass the FormType class to use
     * @param int        $selectedTab   the selectedTab value
     *
     * @throws \ReflectionException
     *
     * @return Form the Form as specified
     */
    private function createTabForm($entity_data, string $formTypeClass, int $selectedTab): Form
    {
        // Switching the route on shortname of form type class ...
        // to match entity<->form ;)
        $reflect = new ReflectionClass($formTypeClass);
        switch ($reflect->getShortName()) {
            case 'MMUserProfileTypePictureOnly':
                $targetRoute = 'restaurant_profile_manager_update_profile_picture';
                break;
            case 'RestaurantProfileBasicType':
                $targetRoute = 'restaurant_profile_manager_update_basic';
                break;
            case 'RestaurantProfileBusinessType':
                $targetRoute = 'restaurant_profile_manager_update_business';
                break;
            case 'RestaurantProfileAddressType':
                $targetRoute = 'restaurant_profile_manager_update_address';
                break;
            case 'RestaurantImageType':
                $targetRoute = 'restaurant_profile_manager_add_picture';
                break;
            case 'RestaurantFileType':
                $targetRoute = 'restaurant_profile_manager_add_file';
                break;
            case 'RestaurantProfileSettingsType':
                $targetRoute = 'restaurant_profile_manager_update_settings';
                break;
            case 'MMUserProfileTypePayment':
                $targetRoute = 'restaurant_profile_manager_update_payment';
                break;
            default:
                $targetRoute = 'restaurant_profile_manager_update';
                break;
        }
        // create and return ...
        return $this->createForm(
            $formTypeClass,
            $entity_data,
            array(
                'action' => $this->generateUrl(
                    $targetRoute,
                    array('selectedTab' => $selectedTab)
                ),
                'method' => 'POST',
            )
        );
    }

    /**
     * Uses the Request to process the MMRestaurantProfile with the specified FormTypeClass.
     * Enables the use of different FormTypeClasses on RestaurantProfile.
     *
     * @param Request $request       the request to process
     * @param string  $formTypeClass the FormType CLASS to use
     *
     * @return RedirectResponse redirects the use to the ProMealManager-SelectedTab
     */
    private function processRestaurantProfileWithForm(Request $request, string $formTypeClass): RedirectResponse
    {
        /** @var MMUser $user */
        $user = $this->getUser();
        /** @var MMRestaurantProfile $restaurantProfile */
        $restaurantProfile = $user->getRestaurantProfile();
        /** @var Form $restaurantProfileForm */
        $restaurantProfileForm = $this->createForm($formTypeClass, $restaurantProfile);

        if ($restaurantProfileForm->isSubmitted() && $restaurantProfileForm->isValid()) {
            $this->getDoctrine()->getManager()->persist($restaurantProfile);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute(
            'api_restaurant_profile_manager',
            array(
                'selectedTab' => $this->getSelectedTab($request),
            )
        );
    }

    /**
     * Uses the Request to process the MMRestaurantProfile with the specified FormTypeClass.
     * Enables the use of different FormTypeClasses on RestaurantProfile.
     *
     * @param Request $request       the request to process
     * @param string  $formTypeClass the FormType CLASS to use
     *
     * @throws \Exception
     *
     * @return RedirectResponse redirects the use to the ProMealManager-SelectedTab
     */
    private function processSinglePageWithForm(Request $request, string $formTypeClass): RedirectResponse
    {
        /** @var RestaurantService $restaurantService */
        $restaurantService = $this->get('api.restaurant.service');
        /** @var MMUser $user */
        $user = $this->getUser();
        /** @var MMRestaurantProfile $restaurantProfile */
        $restaurantProfile = $user->getRestaurantProfile();
        /** @var MMUserProfile $userProfile */
        $userProfile = $user->getProfile();

        $postData = $request->get('mmuserbundle_mmrestaurantprofile_single_page_type');

        /** @var Form $restaurantProfileForm */
        $restaurantProfileForm = $this->createForm($formTypeClass);
        $restaurantProfileForm->handleRequest($request);

        if ($restaurantProfileForm->isSubmitted() && $restaurantProfileForm->isValid()) {
            $restaurantProfile->setCompany($postData['companyName']);

            $day = $postData['ownerBirthday']['day'];
            $month = $postData['ownerBirthday']['month'];
            $year = $postData['ownerBirthday']['year'];

            $birthday = new DateTime("$day.$month.$year");
            $restaurantProfile->setBirthday($birthday);
            $restaurantProfile->setName($postData['name']);
            $restaurantProfile->setCountry($postData['ownerCountry']);
            $restaurantProfile->setNationality($postData['ownerNationality']);
            $restaurantProfile->setTaxID($postData['taxID']);
            $restaurantProfile->setCommercialRegisterNumber($postData['commercialRegisterNumber']);
//            $restaurantProfile->setLocationString($postData['locationString']);
            $restaurantProfile->setLegalRepresentativeAddressLine1($postData['addressLine1']);
            $restaurantProfile->setLegalRepresentativeAddressLine2($postData['addressLine2']);
            $restaurantProfile->setLegalRepresentativeCity($postData['legalRepresentativeCity']);
            $restaurantProfile->setLegalRepresentativePostalCode($postData['legalRepresentativePostalCode']);
            $restaurantProfile->setLegalRepresentativeRegion($postData['legalRepresentativeRegion']);
//            $restaurantProfile->setAuthorizedRepresentative($data['holderName']);
            $restaurantProfile->setLegalRepresentativeFirstName($postData['firstName']);
            $restaurantProfile->setLegalRepresentativeLastName($postData['lastName']);
            $restaurantProfile->setContactPhone($postData['contactPhone']);
            $restaurantProfile->setBankIBAN($postData['bankIBAN']);
            $restaurantProfile->setDefaultCurrency($postData['defaultCurrency']);
            $restaurantProfile->setTaxRate($postData['taxRate']);
            $restaurantProfile->setDescription($postData['description']);
//            $restaurantProfile->setContactAddress($data['contactAddress']);
            $restaurantProfile->setContactEmail($postData['contactEmail']);

            $this->getDoctrine()->getManager()->persist($userProfile);
            $this->getDoctrine()->getManager()->persist($restaurantProfile);
            $this->getDoctrine()->getManager()->flush();
        }

        if (!$restaurantProfileForm->isValid()) {
            $errorArr = $restaurantProfileForm->getErrors();
            foreach ($errorArr as $key => $error) {
                $this->addFlash(FlashTypes::$DANGER, $error->getMessage());
            }
        }

        // Try to create all mangopay ID's using data from the profiles connected to specified user.
        $result = $restaurantService->getOrCreateMangopayIDs($user);
        if (!$result) {
            // NOT VALID, notify user with toaster
            $this->addFlash(FlashTypes::$DISMISSABLE,
                $this->trans('paymentprofile.validation.nok')
            );
        }

        return $this->redirectToRoute(
            'api_restaurant_profile_manager'
        );
    }

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
     * Uses the Request to process the MMUserSettings with the specified FormTypeClass.
     * Enables the use of different FormTypeClasses on MMUserSettings.
     *
     * @param Request $request       the request to process
     * @param string  $formTypeClass the FormType CLASS to use
     *
     * @return RedirectResponse redirects the use to the ProMealManager-SelectedTab
     */
    private function processUserSettingsWithForm(Request $request, string $formTypeClass): RedirectResponse
    {
        /** @var MMUserSettings $userSettings */
        $userSettings = $this->getUser()->getSettings();
        $form = $this->createForm($formTypeClass, $userSettings);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($userSettings);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute(
            'api_userprofile_manager',
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

    /**
     * Returns the existing RestaurantAddress of the specified MMRestaurantProfile OR creates a new RestaurantAddress
     * AND maps it to the MMRestaurantProfile entity and returns that one.
     *
     * @param MMRestaurantProfile $restaurantProfile the RestaurantProfile to check for an RestaurantAddress
     *
     * @return RestaurantAddress the existing or a new "empty" RestaurantAddress
     */
    private function getOrCreateNewRestaurantAddress(MMRestaurantProfile $restaurantProfile): RestaurantAddress
    {
        if ($restaurantProfile->hasAddress()) {
            return $restaurantProfile->getAddress();
        }

        $entityManager = $this->getDoctrine()->getManager();
        $newAddress = new RestaurantAddress();
        $restaurantProfile->addAddress($newAddress);
        $entityManager->persist($restaurantProfile);
        $entityManager->flush();

        return $newAddress;
    }

    private function createSinglePageFormFromUser(MMUser $user)
    {
        // $formTypeClass = RestaurantProfileBasicType::class;
        $formTypeClass = RestaurantProfileSinglePageType::class;
        $entity_data['companyName'] = $user->getRestaurantProfile()->getCompany();
        $entity_data['name'] = $user->getRestaurantProfile()->getName();
        $entity_data['ownerBirthday'] = $user->getRestaurantProfile()->getBirthday();
        $entity_data['ownerCountry'] = $user->getRestaurantProfile()->getCountry();
        $entity_data['ownerNationality'] = $user->getRestaurantProfile()->getNationality();
        $entity_data['addressLine1'] = $user->getRestaurantProfile()->getLegalRepresentativeAddressLine1();
        $entity_data['addressLine2'] = $user->getRestaurantProfile()->getLegalRepresentativeAddressLine2();
        $entity_data['legalRepresentativeCity'] = $user->getRestaurantProfile()->getLegalRepresentativeCity();
        $entity_data['legalRepresentativePostalCode'] = $user->getRestaurantProfile()->getLegalRepresentativePostalCode();
        $entity_data['legalRepresentativeRegion'] = $user->getRestaurantProfile()->getLegalRepresentativeRegion();
        $entity_data['taxID'] = $user->getRestaurantProfile()->getTaxID();
        $entity_data['commercialRegisterNumber'] = $user->getRestaurantProfile()->getCommercialRegisterNumber();
        $entity_data['locationString'] = $user->getRestaurantProfile()->getLocationString(); // Does not appear
//        $entity_data['holderName'] = $user->getRestaurantProfile()->getAuthorizedRepresentative();
        $entity_data['firstName'] = $user->getRestaurantProfile()->getLegalRepresentativeFirstName();
        $entity_data['lastName'] = $user->getRestaurantProfile()->getLegalRepresentativeLastName();
        $entity_data['contactPhone'] = $user->getRestaurantProfile()->getContactPhone();
        $entity_data['bankIBAN'] = $user->getRestaurantProfile()->getBankIBAN(); // Right location i'm gett IBAN from?
        $entity_data['defaultCurrency'] = $user->getRestaurantProfile()->getDefaultCurrency();
        $entity_data['taxRate'] = $user->getRestaurantProfile()->getTaxRate();
        $entity_data['description'] = $user->getRestaurantProfile()->getDescription();
        $entity_data['contactAddress'] = $user->getRestaurantProfile()->getContactAddress();
        $entity_data['contactEmail'] = $user->getRestaurantProfile()->getContactEmail();

        // $entity_data['companyName'] = $user->getRestaurantProfile()->getCompany();
        $targetRoute = 'restaurant_profile_manager_single_page_update';

        // create and return ...
        return $this->createForm(
            $formTypeClass,
            $entity_data,
            array(
                'action' => $this->generateUrl(
                    $targetRoute
                ),
                'method' => 'POST',
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

    private function createAddLegalUserForm(MMUser $user)
    {
        /** @var RestaurantPaymentUserLegalType $formTypeClass */
        $formTypeClass = RestaurantPaymentUserLegalType::class;

        // Using values from profiles to pre-fill the form ...
        $formData['Name'] = $user->getRestaurantProfile()->getCompany();

        // Using values from session, if they exist
        if ($this->get('session')->has('LegalUserLastPostData')) {
            $formData = $this->get('session')->get('LegalUserLastPostData');
            $day = $formData['LRBirthday']['day'];
            $month = $formData['LRBirthday']['month'];
            $year = $formData['LRBirthday']['year'];
            $birthday = new DateTime("$day.$month.$year");
            $formData['LRBirthday'] = $birthday;
        }
        // The target to "POST" values to
        $targetURL = $this->generateUrl(
            'restaurant_payment_processing_add_legal_user'
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

    private function createAddBankAccountForm(MMUser $user)
    {
        $formTypeClass = RestaurantPaymentBankAccountType::class;
        $entity_data['companyName'] = $user->getRestaurantProfile()->getCompany();

        $targetRoute = 'restaurant_payment_processing_add_bankaccount';

        // create and return ...
        return $this->createForm(
            $formTypeClass,
            $entity_data,
            array(
                'action' => $this->generateUrl(
                    $targetRoute
                ),
                'method' => 'POST',
            )
        );
    }
}
