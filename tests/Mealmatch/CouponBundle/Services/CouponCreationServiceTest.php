<?php
/**
 * Created by PhpStorm.
 * User: andre
 * Date: 01.11.18
 * Time: 13:32
 */

namespace Mealmatch\CouponBundle\Services;

use Mealmatch\ApiBundle\Exceptions\MealmatchException;
use Mealmatch\CouponBundle\Coupon\CouponData;
use Mealmatch\CouponBundle\Coupon\CouponInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;

class CouponCreationServiceTest extends WebTestCase
{
    private $client;
    private $container;
    public function setUp()
    {
        parent::setUp();
        self::bootKernel();
        $this->client = self::createClient();
        /** @var Container container */
        $this->container = $this->client->getContainer();
    }

    /**
     * Testing the "default" CouponType, e.g. call the create method without a CouponType specified.
     * @throws \Doctrine\ORM\ORMException
     */
    public function testCreateDefault()
    {
        /** @var CouponCreationService $couponCreateService */
        $couponCreateService = $this->container->get('Mealmatch\CouponBundle\Services\CouponCreationService');
        /** @var CouponData $defaultCoupon */
        $defaultCoupon = $couponCreateService->create(
            array(
                'AvailableAmount' => 10,
                'Title' => 'Mealmatch Weihnachtsguscheincode',
                'Code' => 'Weihnachten2018TestDefault',
                'Description' => 'Ein Test für einen Weihnachtsguschein',
                'Value' => 5.00,
                'Currency' => 'EUR',
                'Unconsumeble' => 'FooBar'
            )
        );
        self::assertInstanceOf('Mealmatch\CouponBundle\Coupon\CouponData', $defaultCoupon,
            'CouponCreationService->create() did not return instanceof CouponData (default)!');
        self::assertTrue($defaultCoupon->getAvailableAmount() === 10);
        self::assertTrue($defaultCoupon->getTitle()==='Mealmatch Weihnachtsguscheincode');
        self::assertTrue($defaultCoupon->getCouponCode() === 'Weihnachten2018TestDefault');
        self::assertTrue($defaultCoupon->getDescription() === 'Ein Test für einen Weihnachtsguschein');
        self::assertTrue($defaultCoupon->getValue() === 5.00);
        self::assertTrue($defaultCoupon->getCurrency() === 'EUR');
    }

    /**
     * Testing that an exception is thrown if a non valid CouponType is requested for creation.
     */
    public function testCreateUnknownCoupon()
    {
        /** @var CouponCreationService $couponCreateService */
        $couponCreateService = $this->container->get('Mealmatch\CouponBundle\Services\CouponCreationService');

        // Next line should throw a MealmatchException
        $this->expectException(MealmatchException::class);
        // This should throw a MealmatchException ...
        $defaultCoupon = $couponCreateService->create(
            array(
                'Code' => 'WrongCouponTypeTest'
            ),
            // ... because of this ...
            '-UNKNOWN-'
        );



    }

    public function testCreateCoupon()
    {
        /** @var CouponCreationService $couponCreateService */
        $couponCreateService = $this->container->get('Mealmatch\CouponBundle\Services\CouponCreationService');
        /** @var CouponInterface $coupon */
        $coupon = $couponCreateService->create(
            array(
                'AvailableAmount' => 1,
                'Title' => 'OneTimeCoupon',
                'Description' => 'OneTimeCouponTest Description',
                'Value' => 5.00,
                'Currency' => 'EUR',
                'Code' => 'OneTimeCouponTestCode'
            )
        );

        self::assertInstanceOf('Mealmatch\CouponBundle\Coupon\CouponData', $coupon,
            'CouponCreationService->create() did not return instanceof CouponData (default)!');
        self::assertTrue($coupon->getTitle()==='OneTimeCoupon',
            'Data ERROR: The coupon title did not match!' . $coupon->getTitle() . '<=>' . 'OneTimeCoupon');

    }
}
