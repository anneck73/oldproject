<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\CouponBundle\Controller;

use Mealmatch\ApiBundle\MealMatch\Traits\ViewData;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CouponManagerController.
 *
 * @Route("/admin/manager")
 */
class CouponManagerController extends Controller
{
    use ViewData;

    /**
     * @Route("/show", name="coupon_manager_show", methods={"GET"})
     */
    public function indexAction(Request $request)
    {
        // initialize the viewData with the current request
        $this->initViewData($request);

        // get all coupons from public coupons service
        $allCoupons = $this->get('PublicCouponService')->listAll();

        // add allCoupons into view
        $this->addObjectToViewData('allCoupons', $allCoupons);

        // Render template with viewData.
        return $this->render(
            '@WEBUI/CouponManager/show.html.twig',
            $this->getViewData()
        );
    }
}
