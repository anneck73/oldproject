<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Repository;

/*
 * MMUserKYCProfileRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
use Doctrine\ORM\EntityRepository;

class MMUserKYCProfileRepository extends EntityRepository
{
    public function findStatusById($Id)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT p.status FROM MMUserBundle:MMUserKYCProfile p WHERE p.id = :id'
            )->setParameter('id', $Id)
            ->getResult();
    }

    public function findAllByMangopayUserID($mangopayUserID)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT p FROM MMUserBundle:MMUserKYCProfile p WHERE p.mangopayUserID = :mangopayUserID'
            )->setParameter('mangopayUserID', $mangopayUserID)
            ->getResult();
    }

    public function deleteByKycId($kycId)
    {
        return $this->getEntityManager()
            ->createQuery(
                'DELETE FROM MMUserBundle:MMUserKYCProfile p WHERE p.kycId =:kycId'
            )->setParameter('kycId', $kycId)
            ->getResult();
    }
}