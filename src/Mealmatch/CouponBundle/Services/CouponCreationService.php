<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\CouponBundle\Services;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Entity\AbstractEntity;
use Mealmatch\ApiBundle\Entity\Coupon\Coupon as CouponEntity;
use Mealmatch\ApiBundle\Entity\Coupon\MealCoupon as MealCouponEntity;
use Mealmatch\ApiBundle\Exceptions\MealmatchException;
use Mealmatch\CouponBundle\Coupon\CouponData;
use Mealmatch\CouponBundle\Coupon\CouponInterface;
use Psr\Log\LoggerInterface as Logger;
use ReflectionMethod;
use Symfony\Component\Translation\Translator;

/**
 * The Class CouponCreationService creates Coupons.
 *
 * It extends the BaseCouponService for common features (entityManager,logger,translator)
 */
class CouponCreationService extends BaseCouponService
{
    /** @var CouponService $couponService */
    private $couponService;

    public function __construct(
        Logger $logger,
        EntityManager $entityManager,
        Translator $translator)
    {
        parent::__construct($logger, $entityManager, $translator);
    }

    /**
     * @param array  $couponData
     * @param string $couponType
     *
     * @throws \Mealmatch\ApiBundle\Exceptions\MealmatchException
     *
     * @return \Mealmatch\CouponBundle\Coupon\CouponInterface
     */
    public function create(array $couponData, $couponType = 'default'): CouponInterface
    {
        $this->logger->debug('Create new Coupon type: '.$couponType);

        // Define the list of allowed "types". Each type matches a factory method name.
        $allowedTypes = array('default');
        // Is the factory method allowed, if not fail with exception!
        if (!\in_array($couponType, $allowedTypes, true)) {
            throw new MealmatchException('Failed to create coupon! CouponData-Type: '.$couponType.' unknown!');
        }

        // CouponData-Data required fields
        if (!\array_key_exists('Code', $couponData)) {
            throw new MealmatchException('Failed to create coupon! CouponData[\'Code\'] is missing!');
        }

        // The method name with 'create' prefix. example 'createAmountCoupon'
        $factoryMethodName = ucfirst('create'.$couponType);
        // Create the reflection method
        try {
            $factoryMethod = new ReflectionMethod(self::class, $factoryMethodName);
        } catch (\ReflectionException $reflectionException) {
            $this->logger->error(
                'Failed to create coupon('.$couponType.') couponData: '.json_encode($couponData));
            throw new MealmatchException('Failed to create coupon!!! '.$reflectionException->getMessage());
        }
        // Overwrite accessor
        if ($factoryMethod->isPrivate()) {
            $factoryMethod->setAccessible(true);
        }
        // Invoke the method with the $couponData provided.
        return $factoryMethod->invoke($this, $couponData, $couponType);
    }

    /**
     * @param array  $couponData
     * @param string $couponType
     *
     * @throws MealmatchException
     *
     * @return CouponInterface
     */
    private function createDefault(array $couponData, string $couponType): CouponInterface
    {
        $this->logger->debug('create CouponType: '.$couponType.' with: '.json_encode($couponData));

        // Create a new coupon entity of specified type (default)
        $couponEntity = new CouponEntity();
        // process the provided data
        $this->processUsingCouponData($couponData, $couponType, $couponEntity);

        // Return new CouponData with persisted new entity in it.
        return new CouponData($couponEntity);
    }

    /**
     * @param array $couponData
     * @param $couponType
     *
     * @throws MealmatchException
     *
     * @return CouponInterface
     */
    private function createMealCoupon(array $couponData, $couponType): CouponInterface
    {
        $this->logger->debug('createMealCoupon with: '.json_encode($couponData));

        // Create a new coupon entity of specified type (MealCoupon)
        $couponEntity = new MealCouponEntity();
        // process the provided data & fill the entity with it and persist the entity
        $this->processUsingCouponData($couponData, $couponType, $couponEntity);

        // Return new CouponData with persisted new entity in it.
        return new CouponData($couponEntity, ApiConstants::COUPON_TYPE_MEAL);
    }

    /**
     * @param array          $couponData
     * @param string         $couponType
     * @param AbstractEntity $couponEntity
     *
     * @throws MealmatchException
     */
    private function processUsingCouponData(array $couponData, string $couponType, AbstractEntity $couponEntity): void
    {
        // Now consuming the rest of the array and fill the new coupon entity calling setters
        foreach ($couponData as $dataKey => $dataValue) {
            $setterName = ucfirst('set'.$dataKey);
            try {
                $setterMethod = new ReflectionMethod($couponEntity, $setterName);
                $setterMethod->invoke($couponEntity, $dataValue);
                // remove it from the array to indicate that is has been consumed.
                unset($couponData[$dataKey]);
            } catch (\ReflectionException $reflectionException) {
                $this->logger->warning('Failed to call '.$setterName.' on CouponEntity!');
            }
        }

        // Do we have "unconsumed" data left in the array?
        if (\count($couponData) > 0) {
            $this->logger->warning('Unprocessed couponData: '.json_encode($couponData));
        }

        // Setting status to new if not set yet
        if (null === $couponEntity->getStatus()) {
            $couponEntity->setStatus(ApiConstants::COUPON_NEW);
        }

        // Write into DB & update $couponEntity
        try {
            $this->entityManager->persist($couponEntity);
            $this->entityManager->flush();
        } catch (OptimisticLockException $optimisticLockException) {
            $this->logger->error(
                'Failed to get DB lock while running create CouponType: '.$couponType.'!');
            throw new MealmatchException('Failed to create CouponType: '.$couponType.'!!! '.$optimisticLockException->getMessage());
        } catch (ORMException $ORMException) {
            $this->logger->error(
                'Failed to write into DB running create CouponType: '.$couponType.'!');
            throw new MealmatchException('Failed to create CouponType: '.$couponType.'!!! '.$ORMException->getMessage());
        }

        $this->logger->debug('Created new CouponEntity(ID:'.$couponEntity->getId().')');
    }
}
