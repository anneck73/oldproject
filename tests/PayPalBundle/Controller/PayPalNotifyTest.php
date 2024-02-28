<?php
/**
 * Copyright (c) 2017. Mealmatch GmbH
 * Author: Wizard <wizard@mealmatch.de>
 */

namespace PayPalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @todo         : Finish PHPDoc!
 * A summary informing the user what the class PaymentControllerTest does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 * @noinspection SuspiciousAssignmentsInspection
 */
class PayPalNotifyTest extends WebTestCase
{

    public function testPaymentNotifySuccess()
    {
        static ::markTestSkipped('We use Mangopay now');
        $client = self::createClient();
        $url = $client->getContainer()->get('router')->generate('paypal_notify', array('hash' => 'TESTABLE'));
        $crawler = $client->request('GET', $url);

        $this->assertTrue(
            $client->getResponse()->isSuccessful(),
            $client->getResponse()->getContent()
        );


    }
}
