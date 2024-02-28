<?php
/**
 * Created by PhpStorm.
 * User: andre
 * Date: 01.11.18
 * Time: 13:32
 */

namespace Mealmatch\CouponBundle\Services;

use Doctrine\ORM\EntityManager;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Mealmatch\ApiBundle\Exceptions\MealmatchException;
use Mealmatch\ApiBundle\MealMatch\UserManager;
use Mealmatch\CouponBundle\Coupon\CouponData;
use MMUserBundle\Entity\MMUser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;

class CouponServiceTest extends WebTestCase
{
    private $client;
    /** @var Container $container */
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
     * Testing coupon redeem
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    public function testRedeemCoupon()
    {
        // Create test data ...
        $mealTicketRedeemTest1 = $this->createMealTicketClaimTestTicket();
        /** @var CouponCreationService $couponCreateService */
        $couponCreateService = $this->container->get('Mealmatch\CouponBundle\Services\CouponCreationService');
        /** @var CouponData $defaultCoupon */
        $defaultCoupon = $couponCreateService->create(
            array(
                'AvailableAmount' => 1,
                'Title' => 'Redeem TEST',
                'Code' => 'FooBar',
                'Description' => 'Redeem TEST',
                'Value' => 5.00,
                'Currency' => 'EUR',
                'Status' => 'active'
            )
        );

        // Execute redem test ...
        $totalPriceBefore = $mealTicketRedeemTest1->getTotalPrice();
        /** @var CouponService $couponService */
        $couponService = $this->container->get('Mealmatch\CouponBundle\Services\CouponService');
        $result = $couponService->redeem($mealTicketRedeemTest1, 'FooBar');

        self::assertTrue(array_key_exists('Coupon',$result), 'Coupon not contained in return result!' .
        'Result=>'.json_encode($result));

        $totalPriceAfterRedeem = $mealTicketRedeemTest1->getTotalPrice();

        self::assertTrue($totalPriceAfterRedeem === 5.00,
            'TotalTicketPrice after Redeem should be 5.00 but is was:'.$totalPriceAfterRedeem);

    }
    /**
     * @return \Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function createMealTicketClaimTestTicket(): \Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
        /** @var UserManager $userManager */
        $userManager = $this->container->get('api.user_manager');
        /** @var MMUser $mmTestRestaurantUser */
        $mmTestRestaurantUser = $userManager->findUserByUsername('MMTestRestaurant');
        /** @var MMUser $mmTestGuest */
        $mmTestGuest = $userManager->findUserByUsername('MMTestGuest');
        /** @var MMUser $mmTestGuest2 */
        $mmTestGuest2 = $userManager->findUserByUsername('MMTestGuest2');

        $proMealService = $this->container->get('api.pro_meal.service');
        $proMeals = $proMealService->findAllByOwner($mmTestRestaurantUser, 1, array(
            'status' => ApiConstants::MEAL_STATUS_RUNNING,
        ));
        /** @var ProMeal $proMeal */
        $proMeal = $proMeals[0];
        $proMeal->addGuest($mmTestGuest);
        $proMeal->addGuest($mmTestGuest2);
        $entityManager->persist($proMeal);
        $entityManager->flush();
        $mealOffer1 = $entityManager->getRepository('ApiBundle:Meal\MealOffer')->findAll()[0];
        // MealTicket for CLAIM-Test
        $mealTicketClaimTest1 = new BaseMealTicket();
        $mealTicketClaimTest1->setHost($mmTestRestaurantUser);
        $mealTicketClaimTest1->setGuest($mmTestGuest);
        $mealTicketClaimTest1->setDescription('Mealticket CLAIM TEST description');
        $mealTicketClaimTest1->setCurrency('EUR');
        $mealTicketClaimTest1->setMmFee(1.50);
        $mealTicketClaimTest1->setBaseMeal($proMeal);
        $mealTicketClaimTest1->setPrice(10.00);
        $mealTicketClaimTest1->setNumber('1');
        $mealTicketClaimTest1->setNumberOfTickets(1);
        $mealTicketClaimTest1->setSelectedMealOffer($mealOffer1);

        $mealTicketClaimTest1->setTitel('Mealticket CLAIM TEST');
        $mealTicketClaimTest1->setCreatedBy($mmTestGuest);
        $mealTicketClaimTest1->setPaymentType('sofort');
        $mealTicketClaimTest1->setStatus(ApiConstants::MEAL_TICKET_STATUS_CREATED);

        $entityManager->persist($mealTicketClaimTest1);
        $entityManager->persist($proMeal);
        $entityManager->flush();
        return $mealTicketClaimTest1;
    }
}
