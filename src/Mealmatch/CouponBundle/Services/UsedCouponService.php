<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\CouponBundle\Services;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Mealmatch\ApiBundle\Entity\Coupon\UsedCoupon;
use Mealmatch\ApiBundle\Exceptions\MealmatchException;
use Mealmatch\CouponBundle\Coupon\CouponInterface;
use Mealmatch\CouponBundle\Coupon\UsedCouponData;
use Mealmatch\CouponBundle\Coupon\UsedCouponInterface;

class UsedCouponService extends BaseCouponService
{
    /**
     * @param string $couponCode - the coupon code to be invalidated(through redeem)
     *
     * @throws mealmatchException - if persistence fails
     *
     * @return UsedCoupon - the used coupon entry
     */
    public function create(string $couponCode): UsedCoupon
    {
        $this->logger->debug("Create new UsedCouponEntity witch couponCode: $couponCode!");
        $usedC = new UsedCoupon();
        $usedC->setCode($couponCode);

        try {
            $this->entityManager->persist($usedC);
            $this->entityManager->flush();
        } catch (OptimisticLockException $optimisticLockException) {
            $this->logger->error('Doctrine Error!',
                array(
                    'Error Message' => $optimisticLockException->getMessage(),
                ));
            throw new MealmatchException('Failed to create UsedCoupon: '.$optimisticLockException->getMessage());
        } catch (ORMException $ORMException) {
            $this->logger->error('Doctrine Error!',
                array(
                    'Error Message' => $ORMException->getMessage(),
                ));
            throw new MealmatchException('Failed to create UsedCoupon: '.$ORMException->getMessage());
        }
        $this->logger->debug('Created new UsedCoupon entity(ID:'.$usedC->getId().')!');

        return $usedC;
    }

    public function exists($couponCode): bool
    {
        // returns the coupon entity or NULL if the entity can not be found.
        $usedCoupon = $this->entityManager->getRepository('ApiBundle:Coupon\UsedCoupon')->findOneBy(
            array('code' => $couponCode)
        );

        return null !== $usedCoupon;
    }

    private function restore(CouponInterface $coupon): UsedCouponInterface
    {
        $usedCouponEntity = $this->entityManager->getRepository('ApiBundle:Coupon\UsedCoupon')->findOneBy(
            array('code' => $coupon->getCouponCode())
        );

        return new UsedCouponData($coupon->getCouponEntity(), $usedCouponEntity);
    }
}
