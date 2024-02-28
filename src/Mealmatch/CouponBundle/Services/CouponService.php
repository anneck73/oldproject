<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\CouponBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Mealmatch\ApiBundle\Entity\Coupon\Coupon;
use Mealmatch\ApiBundle\Entity\Coupon\MealCoupon;
use Mealmatch\ApiBundle\Entity\Coupon\UsedCoupon;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
use Mealmatch\ApiBundle\Exceptions\MealmatchException;
use Mealmatch\CouponBundle\Coupon\CouponData;
use Mealmatch\CouponBundle\Coupon\CouponInterface;
use Mealmatch\CouponBundle\Coupon\UsedCouponData;
use Mealmatch\CouponBundle\Coupon\UsedCouponInterface;
use Mealmatch\CouponBundle\Coupon\UsedMealCouponData;

/**
 * The CouponService class redeems coupon's using BaseMealTicket and the coupon code string.
 *
 * It uses the entity manager to update existing coupon's and to create UsedCoupon entites during redeem.
 */
class CouponService extends BaseCouponService
{
    public function exists($couponCode): bool
    {
        // returns the coupon entity or NULL if the entity can not be found.
        $couponEntity = $this->entityManager->getRepository('ApiBundle:Coupon\Coupon')->findOneBy(
            array('code' => $couponCode)
        );
        // returns the coupon entity or NULL if the entity can not be found.
        $mealCouponEntity = $this->entityManager->getRepository('ApiBundle:Coupon\MealCoupon')->findOneBy(
            array('code' => $couponCode)
        );

        if ((null !== $couponEntity) || (null !== $mealCouponEntity)) {
            return true;
        }

        return false;
    }

    /**
     * Updates the MealTicket and creates a UsedCoupon entry.
     *
     * @param BaseMealTicket $mealTicket
     * @param string         $couponCode
     *
     * @throws ORMException
     *
     * @return array
     */
    public function redeem(BaseMealTicket $mealTicket, string $couponCode): array
    {
        // check coupon code and return error

        if (!$this->exists($couponCode)) {
            return array(
                'ERROR' => "CouponCode $couponCode does not exist!",
            );
        }

        /** @var CouponInterface $coupon */
        $coupon = $this->restore($couponCode);

        if (!$coupon->isActive()) {
            return array(
                'ERROR' => "CouponCode $couponCode is not active!",
            );
        }

        if (!$coupon->isAvailable()) {
            return array(
                'ERROR' => "CouponCode $couponCode is not available!",
            );
        }

        // ALL GOOD, redeem one time
        try {
            /** @var UsedCouponInterface $redeemedCoupon */
            $redeemedCoupon = $this->redeemCoupon($coupon);
        } catch (MealmatchException $e) {
            die($e->getMessage());
        }

        $mealTicket->setCoupon($redeemedCoupon->getUsedCouponEntity());

        $this->entityManager->persist($mealTicket);

        $this->entityManager->flush();

        return array(
            'Coupon' => $coupon,
            'UsedCoupon' => $redeemedCoupon,
        );
    }

    public function listAll(): Collection
    {
        $allCoupons = $this->entityManager->getRepository('ApiBundle:Coupon\Coupon')->findAll();

        return new ArrayCollection($allCoupons);
    }

    private function restore(string $couponCode): CouponInterface
    {
        $couponEntity = $this->entityManager->getRepository('ApiBundle:Coupon\Coupon')->findOneBy(
            array('code' => $couponCode)
        );
        if (null === $couponEntity) {
            $couponEntity = $this->entityManager->getRepository('ApiBundle:Coupon\MealCoupon')->findOneBy(
                array('code' => $couponCode)
            );
        }

        return new CouponData($couponEntity);
    }

    private function redeemCoupon(CouponInterface $coupon): UsedCouponInterface
    {
        // get the entity from CouponInterface
        $couponEntity = $coupon->getCouponEntity();
        $className = '-/-';
        try {
            $tempCE = new \ReflectionClass($coupon);
            $className = $tempCE->getShortName();
        } catch (\ReflectionException $e) {
            die('Doh! - '.$e->getMessage());
        }

        // create a NEW UsedCoupon for this redeem.
        $usedCoupon = new UsedCoupon();
        $usedCoupon->setType($className);
        $usedCoupon->setCode($coupon->getCouponCode());
        $usedCoupon->setValue($coupon->getValue());
        $usedCoupon->setTitle($couponEntity->getTitle());
        $usedCoupon->setDescription($coupon->getCouponEntity()->getDescription().'111');
        $usedCoupon->setRedeemed(1);

        // change availability stats of coupon "once", e.g. redeem one coupon usage
        $coupon->redeem(1);

        try {
            $this->entityManager->beginTransaction();
            $this->entityManager->persist($usedCoupon);
            $this->entityManager->persist($couponEntity);
            $this->entityManager->commit();
            $this->entityManager->flush();
        } catch (OptimisticLockException $optimisticLockException) {
            $this->logger->error('Doctrine Error!',
                array(
                    'Error Message' => $optimisticLockException->getMessage(),
                ));
            $this->entityManager->rollback();
            throw new MealmatchException('Failed to create UsedCoupon: '.$optimisticLockException->getMessage());
        } catch (ORMException $ORMException) {
            $this->logger->error('Doctrine Error!',
                array(
                    'Error Message' => $ORMException->getMessage(),
                ));
            $this->entityManager->rollback();
            throw new MealmatchException('Failed to create UsedCoupon: '.$ORMException->getMessage());
        }

        if ($couponEntity instanceof Coupon) {
            return new UsedCouponData($couponEntity, $usedCoupon);
        }
        if ($couponEntity instanceof MealCoupon) {
            return new UsedMealCouponData($couponEntity, $usedCoupon);
        }
    }
}
