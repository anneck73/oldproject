<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Controller\Coupon;

use Mealmatch\ApiBundle\Exceptions\MealmatchException;
use Mealmatch\ApiBundle\Form\Coupon\CouponWalletAmountType;
use Mealmatch\ApiBundle\MealMatch\Traits\ViewData;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CouponManagerController.
 *
 * @Route("/admin/wallet-manager")
 * @Security("has_role('ROLE_ADMIN')")
 */
class CouponWalletManagerController extends Controller
{
    use ViewData;

    /**
     * @Route("/show", name="coupon_wallet_manager_show", methods={"GET"})
     */
    public function indexAction(Request $request)
    {
        // initialize the viewData with the current request
        $this->initViewData($request);

        // get all coupons from public coupons service
        $allCoupons = $this->get('PublicCouponService')->listAll();
        // add allCoupons into view
        $this->addObjectToViewData('allCoupons', $allCoupons);

        $couponWalletAmountForm = $this->createForm(CouponWalletAmountType::class, array(),
            array(
                'action' => $this->generateUrl(
                    'coupon_wallet_manager_payin_bankwire'
                ),
                'method' => 'POST',
            )
        );
        $this->addObjectToViewData('amountForm', $couponWalletAmountForm->createView());
        $this->addStringToViewData('walletValue', 0.00);

        $walletService = $this->get('PublicMangopayService')->getMangopayWalletService();
        $mealmatchCouponWallet = $walletService->getCouponWallet();
        $this->addObjectToViewData('couponWallet', $mealmatchCouponWallet);

        // Render template with viewData.
        return $this->render(
            '@WEBUI/CouponWalletManager/show.html.twig',
            $this->getViewData()
        );
    }

    /**
     * @param Request $request
     *
     * @return
     *
     * @Route("/show", name="coupon_wallet_manager_payin_bankwire", methods={"POST"})
     */
    public function doPayInBankWire(Request $request): Response
    {
        // postData from the Payment RestaurantPaymentUserLegalType form
        $postData = $request->get('mealmatch_apibundle_couponwallet_amount');
        $amount = $postData['amount'];
        $amountInCent = $amount * 100;

        $payInService = $this->get('PublicMangopayService')->getMangopayPayInService();
        $couponWalletPayIn = $payInService->createCouponWalletPayInDirectWeb($amountInCent);

        $this->get('logger')->err(json_encode($couponWalletPayIn));
        $payInResult = $payInService->doCreatePayInDirectWeb($couponWalletPayIn);

        $mttService = $this->get('MealticketTransactionService');

        // Create the local meal ticket transaction to store the resourceID in
        try {
            $mttService->createFromPayin($payInResult);
        } catch (MealmatchException $mealmatchException) {
            $this->logger->error('Failed to create MTT -> '.$mealmatchException->getMessage());
        }

        if (null !== $payInResult && $payInResult->ExecutionDetails && $payInResult->ExecutionDetails->RedirectURL) {
            return $this->redirect($payInResult->ExecutionDetails->RedirectURL);
        }

        $this->addFlash('warning', 'ERROR: '.$payInResult->ResultMessage);

        return $this->redirectToRoute('coupon_wallet_manager_show');
    }
}
