<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\MangopayBundle\Controller;

use Mealmatch\ApiBundle\Controller\ApiController;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
use Mealmatch\ApiBundle\Entity\Meal\MealTicketTransaction;
use Mealmatch\ApiBundle\Exceptions\MealmatchException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * The PaymentHooksController is setup to receive all Mangopay "Hook" events.
 * Every event received is persisted into the db.
 *
 * /payment/PayIn/Created
 *
 *
 * @Route("payment/");
 */
class PaymentHooksController extends ApiController
{
    /**
     * @Route("{mangoObj}/{mangoEvent}");
     *
     * @param Request    $request
     * @param mixed|null $mangoObj
     * @param mixed|null $mangoEvent
     *
     * @throws MealmatchException
     *
     * @return Response|ResourceNotFoundException
     */
    public function paymentHookAction(Request $request, $mangoObj = null, $mangoEvent = null)
    {
        $this->init();

        $ressourceID = $request->get('RessourceId');
        $eventType = $request->get('EventType');
        $date = $request->get('Date');

        $this->logger->addDebug(
            "paymentHookAction $mangoObj/$mangoEvent resourceID: $ressourceID, eventType: $eventType, Date: $date");

        // IF event type SUFFIX: _created_
        // wait for 1 sec, to have the db finish any writes from creation of MTT's.
        if (strpos($eventType, '_CREATED')) {
            sleep(1);
            if (!$this->hasMTTWithResourceID($ressourceID)) {
                // After 1 sec, no matching MTT was found, failure:
                $this->logger->addWarning('Failed to process Hook, ressourceID('.$ressourceID.') not found!');
                // Return 404 to Mangopay
                return new Response(
                    'RessourceID:'.$ressourceID.' does not exist!',
                    Response::HTTP_NOT_FOUND
                );
            }
        }

//        // Begin checking, sometimes ... we check to early ...
//        $checkCount = 1;
//        // Check 3 times, wait 1 sec for each
//        while ($checkCount <= 3) {
//            if (!$this->hasMTTWithResourceID($ressourceID)) {
//                sleep(1);
//            }
//            ++$checkCount;
//        }
//

        $hookData = array(
            'mangoObj' => $mangoObj,
            'mangoEvent' => $mangoEvent,
            'eventType' => $eventType,
            'rID' => $ressourceID,
            'date' => $date,
        );

        $existingMTT = $this->restoreMTT($ressourceID);
        if ($existingMTT->hasMealticket()) {
            // Restore the mealticket associated to the transaction, should be there because of above validation.
            $this->logger->addDebug('paymentHookAction('.$ressourceID.') try to restore associated Mealticket!');
            $mealTicket = $this->restoreMealticket($ressourceID);
            $this->logger->addDebug('paymentHookAction('.$ressourceID.') restored associated Mealticket->'.$mealTicket->getNumber());

            $newMTT = $this->get('PaymentHookService')->addTransactionFromHookEvent($hookData, $mealTicket);
            $this->get('PaymentHookService')->processMealticketTransaction($newMTT);
        }
        if (null !== $existingMTT) {
            $this->get('PaymentHookService')->processMealticketTransaction($existingMTT);
        }

        $this->logger->addDebug('No MTT found with: '.$ressourceID);

        return new Response('ACK');
    }

    /**
     * Searches all MealTicketTransactions for the specified $ressourceID. (Mangopay Event).
     *
     * @param $ressourceID
     *
     * @return bool
     */
    private function hasMTTWithResourceID($ressourceID): bool
    {
        $this->init();
        $found = $this->em->getRepository('ApiBundle:Meal\MealTicketTransaction')->findOneBy(
            array(
                'resourceId' => $ressourceID,
            )
        );
        // results in true if $found is not NULL.
        return $found instanceof MealTicketTransaction;
    }

    private function restoreMealticket($ressourceID): BaseMealTicket
    {
        $this->init();
        /** @var MealTicketTransaction $foundTrans */
        $foundTrans = $this->em->getRepository('ApiBundle:Meal\MealTicketTransaction')->findOneBy(
            array(
                'resourceId' => $ressourceID,
            )
        );

        return $this->em->getRepository('ApiBundle:Meal\BaseMealTicket')->findOneBy(
            array(
                'id' => $foundTrans->getMealTicket()->getId(),
            )
        );
    }

    private function restoreMTT($ressourceID): MealTicketTransaction
    {
        $this->init();

        return $this->em->getRepository('ApiBundle:Meal\MealTicketTransaction')->findOneBy(
            array(
                'resourceId' => $ressourceID,
            )
        );
    }
}
