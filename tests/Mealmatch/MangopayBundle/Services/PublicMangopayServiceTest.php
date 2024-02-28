<?php
/**
 * Created by PhpStorm.
 * User: andre
 * Date: 13.01.19
 * Time: 18:59
 */

namespace Mealmatch\MangopayBundle\Services;

use MangoPay\MangoPayApi;
use MangoPay\PayInPaymentDetailsCard;
use MangoPay\Transfer;
use Mealmatch\MealmatchKernelTestCase;

class PublicMangopayServiceTest extends MealmatchKernelTestCase
{

    public function testValidateGuestCanReceivePayin()
    {
        $mmTestGuest = $this->em->getRepository('MMUserBundle:MMUser')->findOneBy(
            array('username' => 'MMTestGuest')
        );
        $result = static::$kernel->getContainer()->get('Mealmatch\MangopayBundle\Services\PublicMangopayService')
            ->validateUserCanReceivePayin($mmTestGuest);
        self::assertTrue($result, 'MMTestGuest failed to validateUserCanReceivePayment()!');
    }

    public function testValidateRestaurantCanReceivePayin()
    {
        $mmTestRestaurant = $this->em->getRepository('MMUserBundle:MMUser')->findOneBy(
            array('username' => 'MMTestRestaurant')
        );
        $result = static::$kernel->getContainer()->get('Mealmatch\MangopayBundle\Services\PublicMangopayService')
            ->validateUserCanReceivePayin($mmTestRestaurant);
        self::assertTrue($result, 'MMTestRestaurant failed to validateUserCanReceivePayment()!');
    }

    public function testValidateBankwirePayout()
    {
        $mmTestRestaurant = $this->em->getRepository('MMUserBundle:MMUser')->findOneBy(
            array('username' => 'MMTestRestaurant')
        );
        $result = static::$kernel->getContainer()->get('Mealmatch\MangopayBundle\Services\PublicMangopayService')
            ->validateUserCanReceiveBankwirePayout($mmTestRestaurant);
        self::assertTrue($result, 'MMTestRestaurant failed to validateUserCanReceivePayment()!');
    }

    public function testGuestToHostTransfer()
    {

        $mealTicket = $this->em->getRepository('ApiBundle:Meal\BaseMealTicket')->find(4);

        $hostMangopayWalletBalanceBeforeTransfer = static::$kernel->getContainer()
            ->get('Mealmatch\MangopayBundle\Services\PublicMangopayService')
            ->getMangopayWalletHostBalanceInCent($mealTicket);

        $valuePayedByGuest = $mealTicket->getTotalPriceInCent();

        $guestMangopayWalletBalanceBeforeTransfer = static::$kernel->getContainer()
            ->get('Mealmatch\MangopayBundle\Services\PublicMangopayService')
            ->getMangopayWalletGuestBalanceInCent($mealTicket);

        /** @var Transfer $result */
        $result = static::$kernel->getContainer()->get('Mealmatch\MangopayBundle\Services\PublicMangopayService')
            ->createTransferGuestToHostWallet($mealTicket);
        self::assertInstanceOf('MangoPay\Transfer', $result,
            'CreateTransfer did not create an instace of Transfer!');


        // self::assertTrue($result, 'MMTestRestaurant failed to validateUserCanReceivePayment()!');
        $execResult = static::$kernel->getContainer()->get('Mealmatch\MangopayBundle\Services\PublicMangopayService')
            ->executeTransfer($result);
        self::assertEquals('SUCCEEDED',
            $execResult->Status,
            'No SUCCCESS!!! reason/error:' . json_encode($execResult->ResultMessage));

        $expectedHostValue = $hostMangopayWalletBalanceBeforeTransfer + $valuePayedByGuest;
        $expectedGuestValue = $guestMangopayWalletBalanceBeforeTransfer - $valuePayedByGuest;

        self::assertEquals($expectedHostValue, static::$kernel->getContainer()
            ->get('Mealmatch\MangopayBundle\Services\PublicMangopayService')
            ->getMangopayWalletHostBalanceInCent($mealTicket), 'Balance dont match got ' . static::$kernel
                ->getContainer()->get('Mealmatch\MangopayBundle\Services\PublicMangopayService')
                ->getMangopayWalletHostBalanceInCent($mealTicket). ' insted of ' . $expectedHostValue);

        self::assertEquals($expectedGuestValue, static::$kernel->getContainer()
            ->get('Mealmatch\MangopayBundle\Services\PublicMangopayService')
            ->getMangopayWalletGuestBalanceInCent($mealTicket), 'Balance dont match got ' . static::$kernel
                ->getContainer()->get('Mealmatch\MangopayBundle\Services\PublicMangopayService')
                ->getMangopayWalletGuestBalanceInCent($mealTicket). ' insted of ' . $expectedGuestValue);

    }

    public function testGuestPayin()
    {
        $mealTicket = $this->em->getRepository('ApiBundle:Meal\BaseMealTicket')->find(4);

        $payInService = static::$kernel->getContainer()
            ->get('PublicMangopayService')
            ->getMangopayPayInService();

        $mealTicket->setPaymentType('SOFORT');
        $createdPayIn = $payInService->createPayInDirectWeb($mealTicket);
        $executePayIn = $payInService->doCreatePayInDirectWeb($createdPayIn);

        $result = $executePayIn->Status;

        self::assertEquals('CREATED', $result, 'testGuestPayin Failed: Status should be CREATED but actually is '.$result);
    }

    public function testPayoutToHostBankAccount()
    {
        $mealTicket = $this->em->getRepository('ApiBundle:Meal\BaseMealTicket')->find(4);

        $createdPayOut = static::$kernel->getContainer()
            ->get('PublicMangopayService')
            ->createPayOutToHostBankAccount($mealTicket);
        sleep(1);

        $executedPayout = static::$kernel->getContainer()->get('PublicMangopayService')->getExecutedHostPayOut($createdPayOut);

        $result = $executedPayout->Status;
        self::assertEquals('SUCCEEDED', $result, 'testPayoutToHostBankAccount Failed: Status should be SUCCEEDED but actually is '.$result);
    }
}
