<?php
/**
 * Copyright (c) 2017. Mealmatch GmbH
 * Author: Wizard <wizard@mealmatch.de>
 */

namespace MMApiBundle;


use Doctrine\ORM\EntityManager;
use Mealmatch\ApiBundle\Model\GeoAddressServiceData;
use Mealmatch\ApiBundle\Services\GeoAddressService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class MealAddressServiceTest extends KernelTestCase
{


    /**
     * @todo: Finish PHPDoc!
     * @var GeoAddressService
     */
    private $mealAddressService;


    public function setUp()
    {
        static::bootKernel();
        $this->mealAddressService = static::$kernel->getContainer()->get('api.geo_address.service');
    }

    public function testCreateDoubleByLocationString()
    {
        /** @var GeoAddressServiceData $serviceData */
        $serviceData = $this->mealAddressService->createMealAddressByLocation('Langhansstraße 116, 13086 Berlin', true);
        static::assertTrue($serviceData->isValid(), "ServiceData invalid!!!");

        /** @var GeoAddressServiceData $serviceData2 */
        $serviceData2 = $this->mealAddressService->createMealAddressByLocation('Langhansstraße 116 Berlin', true);
        static::assertTrue($serviceData2->isValid(), "ServiceData invalid!!!");

        /** @var GeoAddressServiceData $serviceData3 */
        $serviceData3 = $this->mealAddressService->createMealAddressByLocation('Berlin Langhansstraße 116', true);
        static::assertTrue($serviceData3->isValid(), "ServiceData invalid!!!");

        /** @var GeoAddressServiceData $serviceData3 */
        $serviceData4 = $this->mealAddressService->createMealAddressByLocation('13086 Berlin Langhansstraße 116', true);
        static::assertTrue($serviceData4->isValid(), "ServiceData invalid!!!");

    }

    public function testCreateByLocationString()
    {
        /** @var GeoAddressServiceData $serviceData */
        $serviceData = $this->mealAddressService->createMealAddressByLocation('Langhansstraße 116, 13086 Berlin');
        static::assertTrue($serviceData->isValid(), "ServiceData invalid!!!");

        $addressString = $serviceData->getMealAddress()->__toString();
        static::assertNotNull($addressString);

    }

    public function testCreateTwiceByLocationString()
    {
        /** @var GeoAddressServiceData $serviceData */
        $serviceData = $this->mealAddressService->createMealAddressByLocation('Langhansstraße 116, 13086 Berlin');
        static::assertTrue($serviceData->isValid(), "ServiceData invalid!!!");

        /** @var GeoAddressServiceData $serviceData2 */
        $serviceData2 = $this->mealAddressService->createMealAddressByLocation('Langhansstraße 116, 13086 Berlin');
        static::assertTrue($serviceData2->isValid(), "ServiceData invalid!!!");

    }

    public function testCreateByLocationStringVariations()
    {
        /** @var GeoAddressServiceData $serviceData */
        $serviceData = $this->mealAddressService->createMealAddressByLocation('13086 Berlin', true);
        static::assertFalse($serviceData->isValid(), "ServiceData IS NOT invalid!!!");

        /** @var GeoAddressServiceData $serviceData2 */
        $serviceData2 = $this->mealAddressService->createMealAddressByLocation('Berlin', true);
        static::assertFalse($serviceData2->isValid(), "ServiceData IS NOT invalid!!!");

        /** @var GeoAddressServiceData $serviceData3 */
        // $serviceData3 = $this->mealAddressService->createByLocation('Berliner Straße', true);
        // static::assertFalse($serviceData3->isValid(), "ServiceData NOT invalid!!!" . $serviceData3->getMealAddress());

        /** @var GeoAddressServiceData $serviceData4 */
        $serviceData4 = $this->mealAddressService->createMealAddressByLocation('Deutschland', true);
        static::assertFalse($serviceData4->isValid(), "ServiceData IS NOT invalid!!!");

        /** @var GeoAddressServiceData $serviceData5 */
        $serviceData5 = $this->mealAddressService->createMealAddressByLocation('13086', true);
        static::assertFalse($serviceData5->isValid(), "ServiceData invalid!!!");

    }
    private function logIn()
    {
        $session = static::$kernel->getContainer()->get('session');

        // the firewall context (defaults to the firewall name)
        $firewall = 'main';

        /** @var EntityManager $em */
        $em = static::$kernel->getContainer()->get('doctrine.orm.default_entity_manager');
        $user = $em->getRepository('MMUserBundle:MMUser')->findOneByUsername('Wizard');

        $token = new UsernamePasswordToken($user, $user->getPassword(), $firewall, array('ROLE_ADMIN'));
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        return $user;

        // $cookie = new Cookie($session->getName(), $session->getId());
        // static::$kernel->getCookieJar()->set($cookie);
    }


}
