<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ServiceTasksBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
use Mealmatch\ApiBundle\Services\MealTicketService;
use Mealmatch\ApiBundle\Services\ProMealService;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class PayoutProMealsService is a helper service for commands in this bundle.
 */
class PayoutProMealsService
{
    /**
     * @var ProMealService
     */
    private $proMealService;
    /**
     * @var MealTicketService
     */
    private $mealTicketService;
    /**
     * @var Logger
     */
    private $logger;

    public function __construct(ProMealService $proMealService, MealTicketService $mealTicketService, Logger $logger)
    {
        $this->proMealService = $proMealService;
        $this->mealTicketService = $mealTicketService;
        $this->logger = $logger;
    }

    public function findProMealPayouts(): Collection
    {
        $today = new \DateTime('today');
        $threeDaysAgo = (new \DateTime())->modify('-3 days');
        // processed tickets
        $processedTickets = array();

        // Get all "payed" MealTickets;
        $allPayedTickets = $this->mealTicketService->findAllPayedWithPayin();
        /** @var BaseMealTicket $ticket */
        foreach ($allPayedTickets as $ticket) {
            $this->logger->debug('Processing Mealticket#'.$ticket->getNumber());
            $ticketStartDateTime = $ticket->getBaseMeal()->getStartDateTime();
            $ticketStartDate = $ticketStartDateTime->format('d.m.y');
            $ticketStartDateTimeStr = $ticketStartDateTime->format('d.m.y');
            $threeDaysAgoStr = $threeDaysAgo->format('d.m.y');
            $todayStr = $today->format('d.m.y');

            $this->logger->debug('>>> StartDateTime<?>Today >>> '.$ticketStartDateTimeStr.'<?>'.$todayStr);
            $isDue = false;
            // 2019-01-25 <= 2019-01-25 15:36:00
            if ($ticketStartDate <= $today && $ticketStartDate >= $threeDaysAgo) {
                $this->logger->debug(">>> Mealticket Payout is due! $threeDaysAgoStr => $ticketStartDate <= $todayStr  Result: $isDue");
                $isDue = true;
            } else {
                $this->logger->debug(">>> NOT PROCESSING, not in time frame! $threeDaysAgoStr => $ticketStartDate <= $todayStr  Result: $isDue");
                $this->logger->debug("<<< NOT PROCESSING, not in time frame! $threeDaysAgoStr => $ticketStartDate <= $todayStr  :$isDue");
            }
            $processedTickets[] = array(
                'ticket' => $ticket,
                'startDateTime' => $ticketStartDateTime,
                'isDue' => $isDue,
                'ticketStatus' => $ticket->getStatus(),
            );
        }

        return new ArrayCollection($processedTickets);
    }
}
