<?php
/**
 * Copyright 2016-2017 MealMatch UG
 *
 * Author: Wizard <wizard@mealmatch.de>
 * Created: 16.05.17 15:08
 */

namespace ApiBundle;


use Doctrine\Common\Collections\ArrayCollection;
use Mealmatch\ApiBundle\Entity\Meal\HomeMeal;
use Mealmatch\ApiBundle\Entity\Meal\MealEvent;
use Mealmatch\ApiBundle\Entity\Meal\MealPart;
use Mealmatch\ApiBundle\Model\GeoAddressServiceData;
use Mealmatch\ApiBundle\Model\HomeMealServiceData;
use Mealmatch\ApiBundle\Services\GeoAddressService;
use Mealmatch\ApiBundle\Services\HomeMealService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class HomeMealServiceTest extends KernelTestCase
{
    /**
     * @var HomeMealService $homeMealService
     */
    private $homeMealService;

    /**
     * @var GeoAddressService $addressService
     */
    private $addressService;

    public function setUp()
    {
        static::bootKernel();
        $this->homeMealService = static::$kernel->getContainer()->get('api.home_meal.service');
        /** @var GeoAddressService addressService */
        $this->addressService = static::$kernel->getContainer()->get('api.geo_address.service');
    }
    public function testCreateHomeMealByServiceWithORMException()
    {
        /**
         * @var HomeMealServiceData $homeMealServiceData
         */
        $this->expectException('Doctrine\DBAL\Exception\NotNullConstraintViolationException');
        $homeMealServiceData = $this->homeMealService->createFromEntity(new HomeMeal());

        self::assertNotNull($homeMealServiceData);
        self::assertTrue($homeMealServiceData->isManaged($homeMealServiceData->getSpecification()), $homeMealServiceData->getErrorsAsJSON());
        self::assertFalse($homeMealServiceData->isValid(), "HomeMealServiceData is valid???!!!" . $homeMealServiceData);
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $myArgument With a *description* of this argument, these may also
     *    span multiple lines.
     * @throws \Mealmatch\ApiBundle\Exceptions\ServiceDataException
     */
    public function testCreateHomeMealByService()
    {
        $testHomeMeal = new HomeMeal();
        $testHomeMeal->setTitle('TestHomeMeal');

        $testHomeMeal->setSharedCost('5.50');
        $mealEvent = new MealEvent();
        $mealEvent->setStartDateTime(new \DateTime('4.2.4242 16:20'));
        $testHomeMeal->addMealEvent($mealEvent);
        /** @var GeoAddressServiceData $data */
        $data = $this->addressService->createMealAddressByLocation('Am Parkfriedhof 28, 48153 Essen');
        $testHomeMeal->addMealAddress($data->getMealAddress());

        /** @var MealPart $main */
        $main = new MealPart();
        $main->setName('Hauptgang');
        $main->setDescription('Nudeln mit Bolognese');
        /** @var MealPart $starter */
        $starter = new MealPart();
        $starter->setName('Vorspeise');
        $starter->setDescription('Suppe');

        $mealParts = new ArrayCollection([$main, $starter]);
        $testHomeMeal->setMealParts($mealParts);

        $testHomeMeal->setDescription('Nudeln mit Bolognese');
        $testHomeMeal->setMealMain('Nudeln mit Bolognese');


        /**
         * @var HomeMealServiceData $homeMealServiceData
         */
        $homeMealServiceData = $this->homeMealService->createFromEntity($testHomeMeal);
        self::assertNotNull($homeMealServiceData);
        self::assertTrue($homeMealServiceData->isManaged($homeMealServiceData->getSpecification()), $homeMealServiceData->getErrorsAsJSON());
        self::assertTrue($homeMealServiceData->isValid(), "HomeMealServiceData is invalid!" . $homeMealServiceData->getErrorsAsJSON());
    }

}