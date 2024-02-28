<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Controller\Restaurant;

use MangoPay\User;
use Mealmatch\ApiBundle\Controller\ApiController;
use Mealmatch\ApiBundle\MealMatch\FlashTypes;
use Mealmatch\MangopayBundle\Exceptions\MangopayApiException;
use Mealmatch\MangopayBundle\Services\MangopayBankAccountService;
use Mealmatch\MangopayBundle\Services\MangopayUserService;
use Mealmatch\MangopayBundle\Services\MangopayWalletService;
use MMUserBundle\Entity\MMUserPaymentProfile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The RestaurantPaymentProcessing controller executes calls to Mangopay API.
 *
 * @Route("/u/restaurantpayment/manager")
 * @Security("has_role('ROLE_RESTAURANT_USER')")
 */
class RestaurantPaymentProcessingController extends ApiController
{
    /**
     * @Route("/addLegalUser", name="restaurant_payment_processing_add_legal_user", methods={"POST"})
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function addLegalUser(Request $request): RedirectResponse
    {
        // postData from the Payment RestaurantPaymentUserLegalType form
        $postData = $request->get('mmuserbundle_restaurant_payment_user_legal');
        // Put updated values into session
        $this->get('session')->set('LegalUserLastPostData', $postData);
        // add a new LegalUser using Mangopay
        try {
            $mangopayUserID = $this->addLegalUserMangopay($postData);
        } catch (MangopayApiException $mangopayApiException) {
            // Something went wrong, flash msg is already filled with errors by addLegalUserMangpay.
            return $this->redirectToRoute('restaurant_profile_manager_edit_payment');
        }
        // retrieve the created user from Mangopay using its ID
        $mangopayUser = $this->get('PublicMangopayService')->getMangopayUserService()->getUser($mangopayUserID);
        // add a Mangopay wallet to the mangopay user
        $this->addWalletMangopay($mangopayUser);

        return $this->redirectToRoute('restaurant_profile_manager_edit_payment');
    }

    /**
     * @Route("/addBankaccount", name="restaurant_payment_processing_add_bankaccount", methods={"POST"})
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function addBankAccount(Request $request): RedirectResponse
    {
        $postData = $request->get('mmuserbundle_restaurant_payment_bank_account');

        // Put updated values into session
        $this->get('session')->set('BankAccountLastPostData', $postData);

        /** @var MangopayBankAccountService $mangopayBAService */
        $mangopayBAService = $this->get('PublicMangopayService')->getMangopayBankAccountService();
        $mangopayBA = $mangopayBAService->createBankAccountFromArray($postData);

        /** @var MMUserPaymentProfile $paymentProfile */
        $paymentProfile = $this->getUser()->getPaymentProfile();
        $mangopayID = $paymentProfile->getMangopayID();

        try {
            $newMangopayBA = $mangopayBAService->doCreateBankAccount($mangopayID, $mangopayBA);
            $paymentProfile->setMangopayBankAccountId($newMangopayBA->Id);
            $this->get('doctrine.orm.entity_manager')->persist($paymentProfile);
            $this->get('doctrine.orm.entity_manager')->flush();
        } catch (MangopayApiException $mangopayApiException) {
            $this->addFlash(FlashTypes::$WARNING, $mangopayApiException->getMessage());
        }

        return $this->redirectToRoute('restaurant_profile_manager_edit_payment');
    }

    /**
     * @param $postData
     *
     * @throws MangopayApiException
     *
     * @return int the created mangopay user id
     */
    protected function addLegalUserMangopay($postData): int
    {
        /** @var MangopayUserService $mangopayUserService */
        $mangopayUserService = $this->get('PublicMangopayService')->getMangopayUserService();
        $newUserLegal = $mangopayUserService->createUserLegalFromArray($postData);

        try {
            $mangopayUserID = $mangopayUserService->doCreateUserLegal($newUserLegal);
            // @todo: safe to payment profile
            /** @var MMUserPaymentProfile $paymentProfile */
            $paymentProfile = $this->getUser()->getPaymentProfile();
            $paymentProfile->setMangopayID($mangopayUserID);
            $this->get('doctrine.orm.entity_manager')->persist($paymentProfile);
            $this->get('doctrine.orm.entity_manager')->flush();
        } catch (MangopayApiException $mangopayApiException) {
            $this->addFlash(FlashTypes::$WARNING, $mangopayApiException->getMessage());
            throw new MangopayApiException('Failed to addLegalUserMangoapy: '.
                $mangopayApiException->getMessage());
        }

        return $mangopayUserID;
    }

    /**
     * @param $postData
     */
    protected function addWalletMangopay(User $mangopayUser): void
    {
        /** @var MangopayWalletService $mangopayWalletService */
        $mangopayWalletService = $this->get('PublicMangopayService')->getMangopayWalletService();
        $wallet = $mangopayWalletService->doCreateWallet($mangopayUser);
        /** @var MMUserPaymentProfile $paymentProfile */
        $paymentProfile = $this->getUser()->getPaymentProfile();
        $paymentProfile->setMangopayWalletID($wallet->Id);
        $this->get('doctrine.orm.entity_manager')->persist($paymentProfile);
        $this->get('doctrine.orm.entity_manager')->flush();
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

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }

        return $errors;
    }
}
