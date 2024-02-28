<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\MangopayBundle\Services;

use Doctrine\ORM\EntityManager;
use MMUserBundle\Entity\MMUserPaymentProfile;

class MangopayKYCService
{
    /**
     * Returns a status array for all available KYC document types in one for a specific payment profile.
     *
     * @param EntityManager        $entityManager
     * @param MMUserPaymentProfile $paymentProfile
     *
     * @return array
     */
    public function fetchDocumentStatusAll(EntityManager $entityManager, MMUserPaymentProfile $paymentProfile)
    {
        $userMangopayId = $paymentProfile->getMangopayID();

        $kycDocTypes = array('IDENTITY_PROOF', 'REGISTRATION_PROOF', 'ARTICLES_OF_ASSOCIATION', 'SHAREHOLDER_DECLARATION');
        $kycStatus = array();

        for ($i = 0; $i < 4; ++$i) {
            $id = $entityManager->getRepository('MMUserBundle:MMUserKYCProfile')->findOneBy(
                array(
                    'mangopayUserID' => $userMangopayId,
                    'kycDocSubmitted' => $kycDocTypes[$i],
                )
            );
            if (null === $id) {
                $kycStatus[$i] = 'none';
            } else {
                $status = $entityManager->getRepository('MMUserBundle:MMUserKYCProfile')->findStatusById($id);
                $kycStatus[$i] = $status[0]['status'];
            }
        }

        return $kycStatus;
    }
}
