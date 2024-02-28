<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Controller;

use Mealmatch\ApiBundle\Controller\ApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @todo: Finish PHPDoc!
 *
 * @Route("kyc/status")
 */
class UserKycStatusController extends ApiController
{
    /**
     * Shows the kyc details to the user.
     *
     * @param Request $request
     * @Route("/show", name="userprofile_kyc_status_show")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showManager(Request $request)
    {
        $entityManager = $this->get('doctrine.orm.default_entity_manager');
        $allUsers = $entityManager->getRepository('MMUserBundle:MMUser')->findAll();
        $i = 0;
        foreach ($allUsers as $allUser) {
            $userMangopayId = $allUser->getPaymentProfile()->getMangopayID();
            //For testing
            if (null !== $userMangopayId) {
                $userKycPofile = $entityManager->getRepository('MMUserBundle:MMUserKYCProfile')->findAllByMangopayUserID($userMangopayId);
                $roles = $allUser->getRoles();
                $kycStatus = $allUser->getOverallKycStatus();

                $kycUserData = array();
                for ($j = 0; $j < \count($userKycPofile); ++$j) {
                    $kycProfile = array(
                        'kycId' => $userKycPofile[$j]->getKycId(),
                        'kycDocType' => $userKycPofile[$j]->getKycDocType(),
                        'status' => $userKycPofile[$j]->getStatus(),
                        'id' => $userKycPofile[$j]->getId(),
                        'kycDocSubmitted' => $userKycPofile[$j]->getKycDocSubmitted(),
                    );
                    $kycUserData[$j] = $kycProfile;
                }

                $renderViewData = array(
                    'userId' => $allUser->getId(),
                    'userName' => $allUser->getUsername(),
                    'mangopayId' => $userMangopayId,
                    'status' => $kycStatus,
                    /*'kycData' => $userKycPofile,*/
                    'userKycPofile' => $kycUserData,
                )
                ;
                $kycProfiles[$i] = $renderViewData;
                ++$i;
            }
        }

        return $this->render('@MMUser/KYC/kyc_status.html.twig', array('kycProfiles' => $kycProfiles));
    }

    /**
     * Shows the user specific kyc details to the admin.
     *
     * @param Request $request
     * @Route("/data", name="userprofile_d_show")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function test(Request $request)
    {
        $kycData = $request->get('request');

        return $this->render('@MMUser/KYC/kyc.html5.twig', array('userData' => $kycData['userKycPofile']));
    }
}
