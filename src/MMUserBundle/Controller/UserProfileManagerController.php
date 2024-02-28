<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Mealmatch\ApiBundle\Controller\ApiController;
use Mealmatch\ApiBundle\Exceptions\MealmatchException;
use Mealmatch\ApiBundle\MealMatch\FlashTypes;
use MMUserBundle\Entity\MMUser;
use MMUserBundle\Entity\MMUserPaymentProfile;
use MMUserBundle\Entity\MMUserProfile;
use MMUserBundle\Entity\MMUserSettings;
use MMUserBundle\Form\MMUserProfileSinglePageType;
use MMUserBundle\Form\MMUserProfileTypeAddr;
use MMUserBundle\Form\MMUserProfileTypeBasic;
use MMUserBundle\Form\MMUserProfileTypePayment;
use MMUserBundle\Form\MMUserProfileTypePictureOnly;
use MMUserBundle\Form\MMUserProfileTypePrivate;
use MMUserBundle\Form\MMUserSettingsType;
use ReflectionClass;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class UserProfileManagerController does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 *
 * @Route("profile/manager")
 * @Security("has_role('ROLE_USER')")
 */
class UserProfileManagerController extends ApiController
{
    /**
     * Shows the main management interface to the user.
     *
     * @param Request $request
     * @Route("/show", name="userprofile_manager_show")
     * @Route("/", name="api_userprofile_manager")
     * @Method("GET")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showManager(Request $request)
    {
        /** @var MMUser $user */
        $user = $this->getUser();
        /** @var MMUserProfile $userProfile */
        $userProfile = $user->getProfile();
        $profilePercentage = $this->getPercentageFilled($userProfile, MMUserProfile::class, 1);
        /** @var MMUserPaymentProfile $userPaymentProfile */
        $userPaymentProfile = $user->getPaymentProfile();
        // @todo: remove this and create a userUpdateTask() to fix missig settings on user.
        if (null === $userPaymentProfile) {
            $paymentProfile = new MMUserPaymentProfile();
            $user->setPaymentProfile($paymentProfile);
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();
        }
        /** @var MMUserSettings $userSettings */
        $userSettings = $user->getSettings();
        // @todo: remove this and create a userUpdateTask() to fix missig settings on user.
        if (null === $userSettings) {
            $userSettings = new MMUserSettings();
            $user->setSettings($userSettings);
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();
        }

        $formTabPicture = $this->createTabForm($userProfile, MMUserProfileTypePictureOnly::class, 1);
        $formTabOne = $this->createTabForm($userProfile, MMUserProfileTypeBasic::class, 1);
        $formTabTwo = $this->createTabForm($userProfile, MMUserProfileTypeAddr::class, 2);
        $formTabThree = $this->createTabForm($userProfile, MMUserProfileTypePrivate::class, 3);
        $formTabFour = $this->createTabForm($userSettings, MMUserSettingsType::class, 4);
        $formTabPay = $this->createTabForm($userPaymentProfile, MMUserProfileTypePayment::class, 5);
        $formNewUI = $this->createTabForm($userProfile, MMUserProfileSinglePageType::class, 1); // we need a createForm without Tab parameter

        // Render view
        $renderViewData = new ArrayCollection(array(
                'selectedTab' => $this->getSelectedTab($request),
                'formTabPic' => $formTabPicture->createView(),
                'formTabOne' => $formTabOne->createView(),
                'formTabTwo' => $formTabTwo->createView(),
                'formTabThree' => $formTabThree->createView(),
                'formTabFour' => $formTabFour->createView(),
                'formTabPay' => $formTabPay->createView(),
                'uProfile' => $userProfile,
                'uSettings' => $userSettings,
                'pPercentage' => $profilePercentage,
                'formSinglePage' => $formNewUI->createView(),
            )
        );

        return $this->render('@WEBUI/profiles/UserProfile/manager.html.twig', $renderViewData->toArray());
    }

    /**
     * @Route("/updatePicture", name="userprofile_manager_update_pic")
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function updatePictureAction(Request $request): RedirectResponse
    {
        return $this->processUserProfileWithForm($request, MMUserProfileTypePictureOnly::class);
    }

    /**
     * @Route("/updatePayment", name="userprofile_manager_update_payment")
     * @Method({"POST"})
     */
    public function updatePaymentAction(Request $request)
    {
        return $this->processUserPaymentProfileWithForm($request, MMUserProfileTypePayment::class);
    }

    /**
     * @Route("/updateBasic", name="userprofile_manager_update_basic")
     * @Method({"POST"})
     */
    public function updateBasicAction(Request $request)
    {
        return $this->processUserProfileWithForm($request, MMUserProfileTypeBasic::class);
    }

    /**
     * @Route("/updateAddr", name="userprofile_manager_update_addr")
     * @Method({"POST"})
     */
    public function updateAddrAction(Request $request)
    {
        return $this->processUserProfileWithForm($request, MMUserProfileTypeAddr::class);
    }

    /**
     * @Route("/updatePrivate", name="userprofile_manager_update_private")
     * @Method({"POST"})
     */
    public function updatePrivateAction(Request $request)
    {
        return $this->processUserProfileWithForm($request, MMUserProfileTypePrivate::class);
    }

    /**
     * @Route("/updateSettings", name="userprofile_manager_update_settings")
     * @Method({"POST"})
     */
    public function updateSettingsAction(Request $request)
    {
        return $this->processUserSettingsWithForm($request, MMUserSettingsType::class);
    }

    /**
     * @Route("/updateSinglePageProfile", name="userprofile_manager_update_single_page_profile")
     * @Method({"POST"})
     */
    public function updateSinglePageProfile(Request $request)
    {
        return $this->processSinglePageProfileWithForm($request, MMUserProfileSinglePageType::class);
    }

    private function createTabForm($entity_data, string $formTypeClass, int $selectedTab): Form
    {
        // Switching the route on shortname of form type class ...
        // to match entity<->form ;)
        $reflect = new ReflectionClass($formTypeClass);
        switch ($reflect->getShortName()) {
            case 'MMUserProfileTypePayment':
                $targetRoute = 'userprofile_manager_update_payment';
                break;
            case 'MMUserProfileTypePictureOnly':
                $targetRoute = 'userprofile_manager_update_pic';
                break;
            case 'MMUserProfileTypeBasic':
                $targetRoute = 'userprofile_manager_update_basic';
                break;
            case 'MMUserProfileTypeAddr':
                $targetRoute = 'userprofile_manager_update_addr';
                break;
            case 'MMUserProfileTypePrivate':
                $targetRoute = 'userprofile_manager_update_private';
                break;
            case 'MMUserSettingsType':
                $targetRoute = 'userprofile_manager_update_settings';
                break;
            case 'MMUserProfileSinglePageType':
                $targetRoute = 'userprofile_manager_update_single_page_profile';
                break;
            default:
                throw new MealmatchException('Default: unknown FormType: '.$reflect->getShortName());
                break;
        }

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

    private function processUserProfileWithForm(Request $request, string $formTypeClass): RedirectResponse
    {
        /** @var MMUser $user */
        $user = $this->getUser();
        /** @var MMUserProfile $userProfile */
        $userProfile = $this->getUser()->getProfile();
        $variableForm = $this->createForm($formTypeClass, $userProfile);
        $variableForm->handleRequest($request);

        if ($variableForm->isSubmitted() && $variableForm->isValid()) {
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

        /** @var FormErrorIterator $errors */
        $errors = $variableForm->getErrors(true);
        foreach ($errors as $error) {
            $params = implode(', ', $error->getMessageParameters());
            $this->addFlash('danger', $params.$error->getMessage());
        }

        return $this->redirectToRoute(
            'api_userprofile_manager',
            array(
                'selectedTab' => $this->getSelectedTab($request),
                'validationErrors' => $errors,
            )
        );
    }

    private function processUserSettingsWithForm(Request $request, string $formTypeClass): RedirectResponse
    {
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

    private function processUserPaymentProfileWithForm(Request $request, string $formTypeClass): RedirectResponse
    {
        /** @var MMUser $user */
        $user = $this->getUser();
        $userPaymentProfile = $user->getPaymentProfile();
        $form = $this->createForm($formTypeClass, $userPaymentProfile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($userPaymentProfile);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute(
            'api_userprofile_manager',
            array(
                'selectedTab' => $this->getSelectedTab($request),
            )
        );
    }

    private function processSinglePageProfileWithForm(Request $request, string $formTypeClass)
    {
        /** @var MMUser $user */
        $user = $this->getUser();
        $userProfile = $user->getProfile();
        $form = $this->createForm($formTypeClass, $userProfile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($userProfile);
            $this->getDoctrine()->getManager()->flush();
        }
        // Validate and report validation errors via flash toaster.
        // create empty array to be filled
        $restaurantValidation = array();
        $homeValidation = array();
        if ($user->hasRole('ROLE_RESTAURANT_USER')) {
            $restaurantValidation[] = $this->validateUserProfileForRestaurantRole($userProfile);
        }
        if ($user->hasRole('ROLE_HOME_USER')) {
            $homeValidation[] = $this->validateUserProfileForGuestRole($userProfile);
        }
        $validations = array_merge($homeValidation, $restaurantValidation);
        foreach ($validations as $validationKey => $validationValue) {
            if (\array_key_exists('OK', $validationValue)) {
                $this->addFlash(FlashTypes::$INFO, implode("','", $validationValue));
            } else {
                $this->addFlash(FlashTypes::$DANGER, implode("','", $validationValue));
            }
        }

        return $this->redirectToRoute(
            'api_userprofile_manager');
    }

    private function validateUserProfileForRestaurantRole(MMUserProfile $userProfile): array
    {
        if (
            null === $userProfile->getFirstName() or
            null === $userProfile->getLastName() or
            null === $userProfile->getCountry() or
            // null === $userProfile->getAreaCode() or
            null === $userProfile->getCity() or
            null === $userProfile->getNationality() or
            null === $userProfile->getAddressLine1() or
            null === $userProfile->getBirthday() or
            null === $userProfile->getGender()
        ) {
            return array('ERROR' => $this->get('translator')->trans('flashbag.error.restaurantuservalidation', array(), 'Mealmatch'));
        }

        return array('OK' => $this->get('translator')->trans('flashbag.ok.restaurantuservalidation', array(), 'Mealmatch'));
    }

    private function validateUserProfileForGuestRole(MMUserProfile $userProfile): array
    {
        if (
            null === $userProfile->getFirstName() or
            null === $userProfile->getLastName() or
            null === $userProfile->getNationality() or
            null === $userProfile->getBirthday() or
            null === $userProfile->getGender() or
            null === $userProfile->getCountry()
        ) {
            return array('NOK' => $this->get('translator')->trans('flashbag.error.uservalidation', array(), 'Mealmatch'));
        }

        return array('OK' => $this->get('translator')->trans('flashbag.ok.uservalidation', array(), 'Mealmatch'));
    }
}
