<?php
/**
 * Copyright 2016-2017 MealMatch UG
 *
 * Author: Wizard <wizard@mealmatch.de>
 * Created: 16.05.17 15:08
 */

namespace ApiBundle;


use Mealmatch\ApiBundle\Entity\Meal\MealOffer;
use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Mealmatch\ApiBundle\Entity\Meal\ProMealPart;
use Mealmatch\ApiBundle\Model\GeoAddressServiceData;
use Mealmatch\ApiBundle\Model\ProMealServiceData;
use Mealmatch\ApiBundle\Services\GeoAddressService;
use Mealmatch\ApiBundle\Services\ProMealService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProMealServiceTest extends KernelTestCase
{

    /**
     * @var ProMealService $proMealService
     */
    private $proMealService;

    /**
     * @var GeoAddressService $addressService
     */
    private $addressService;

    public function setUp()
    {
        static::bootKernel();
        $this->proMealService = static::$kernel->getContainer()->get('api.pro_meal.service');
        /** @var GeoAddressService addressService */
        $this->addressService = static::$kernel->getContainer()->get('api.geo_address.service');

    }

    public function testCreateProMealByService()
    {
        static::markTestSkipped('Test needs re-write!');
        $testProMeal = new ProMeal();
        $testProMeal->setTitle('TestProMeal');
        $testProMeal->setDescription('TestProMealBeschreibung');
        $testProMeal->setTableTopic('TestProMeal');
        $testProMeal->setSharedCost('5.50');
        $testProMeal->setStartDateTime(new \DateTime('4.2.4242 16:20'));

        /** @var GeoAddressServiceData $data */
        $data = $this->addressService->createMealAddressByLocation('48153 Essen, Am Parkfriedhof 28', true);
        $testProMeal->addMealAddress($data->getMealAddress());

        /** @var MealOffer $offer1 */
        $offer1 = new MealOffer();
        $offer1->setName('Pizza + Cola');
        $offer1->setDescription('Gibt Pizza und Cola');
        $offer1->setPrice(7.50);
        $offer1->setAvailableAmount(15);
        $testProMeal->addMealOffer($offer1);

        /**
         * @var ProMealServiceData $proMealServiceData
         */
        $proMealServiceData = $this->proMealService->createFromEntity($testProMeal);
        // ServiceData should never be null!!!
        self::assertNotNull($proMealServiceData);
        // createFromEntity should produce a managed bean
        self::assertTrue($proMealServiceData->isManaged($proMealServiceData->getSpecification()),
            "ProMealServiceData should contain a managed entity!" . $proMealServiceData->getErrorsAsJSON());
        // ServiceData should be valid
        self::assertTrue($proMealServiceData->isValid(),
            "ProMealServiceData is invalid! " . $proMealServiceData->getErrorsAsJSON());

    }

    public function testCreateProMealByService2()
    {
        static::markTestSkipped('Test needs re-write!');
        $testProMeal = new ProMeal();
        $testProMeal->setTitle('TestProMeal');
        $testProMeal->setTableTopic('TestProMeal');
        $testProMeal->setSharedCost('5.50');
        $testProMeal->setStartDateTime(new \DateTime('4.2.4242 16:20'));

        /** @var GeoAddressServiceData $data */
        $data = $this->addressService->createMealAddressByLocation('48153 Essen, Am Parkfriedhof 28', true);
        $testProMeal->addMealAddress($data->getMealAddress());

        /** @var MealOffer $offer1 */
        $offer1 = new MealOffer();
        $offer1->setName('Pizza + Cola');
        $offer1->setDescription('Gibt Pizza und Cola');
        $offer1->setPrice(7.50);
        $offer1->setAvailableAmount(15);
        $testProMeal->addMealOffer($offer1);

        /**
         * @var ProMealServiceData $proMealServiceData
         */
        $proMealServiceData = $this->proMealService->createFromEntity($testProMeal);
        self::assertNotNull($proMealServiceData);
        self::assertTrue($proMealServiceData->isManaged($proMealServiceData->getSpecification()), $proMealServiceData->getErrorsAsJSON());
        self::assertTrue($proMealServiceData->isValid(), "ProMealServiceData is invalid! " . $proMealServiceData->getErrorsAsJSON());

    }

    public function testCreateProMealByServiceWithORMException()
    {
        static::markTestSkipped('Test needs re-write!');
        /**
         * @var ProMealServiceData $proMealServiceData
         */
        $this->expectException('Doctrine\DBAL\Exception\NotNullConstraintViolationException');
        $proMealServiceData = $this->proMealService->createFromEntity(new ProMeal());

        self::assertNotNull($proMealServiceData);
        self::assertTrue($proMealServiceData->isManaged($proMealServiceData->getSpecification()), $proMealServiceData->getErrorsAsJSON());
        self::assertFalse($proMealServiceData->isValid(), "ProMealServiceData is valid???!!!" . $proMealServiceData);
    }

    public function testCreateProMealByServiceWithException()
    {
        static::markTestSkipped('Test needs re-write!');
        $testProMeal = new proMeal();
        $testProMeal->setTitle('TestProMeal');
        $testProMeal->setTableTopic('TestProMeal');
        $testProMeal->setSharedCost('5.50');
        $testProMeal->setStartDateTime(new \DateTime('4.2.4242 16:20'));
        /** @var GeoAddressServiceData $data */
        $data = $this->addressService->createMealAddressByLocation('Am Parkfriedhof 28, 48153 Essen');
        $testProMeal->addMealAddress($data->getMealAddress());

        /**
         * @var ProMealServiceData $homeMealServiceData
         */
        $proMealServiceData = $this->proMealService->createFromEntity($testProMeal);

        self::assertNotNull($proMealServiceData);
        self::assertTrue($proMealServiceData->isManaged($proMealServiceData->getSpecification()), $proMealServiceData->getErrorsAsJSON());
        self::assertFalse($proMealServiceData->isValid(), "ProMealServiceData is valid???!!!" . $proMealServiceData);
    }
}