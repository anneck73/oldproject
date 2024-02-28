<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\CalendarBundle\Listener;

use AncaRebeca\FullCalendarBundle\Event\CalendarEvent;
use AncaRebeca\FullCalendarBundle\Model\FullCalendarEvent;
use MealMatch\CalendarBundle\Entity\Calendar\CalendarEvent as MyEvent;

/**
 * @todo: Finish this listener, it does not load anything yet!
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class LoadDataListener does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class LoadDataListener
{
    /**
     * @param CalendarEvent $calendarEvent
     *
     * @return FullCalendarEvent[]
     */
    public function loadData(CalendarEvent $calendarEvent)
    {
        $startDate = $calendarEvent->getStart();
        $endDate = $calendarEvent->getEnd();
        $filters = $calendarEvent->getFilters();

        //You may want do a custom query to populate the events

        // $calendarEvent->addEvent(new MyEvent('Test1', new \DateTime('now')));
        // $calendarEvent->addEvent(new MyEvent('Test2', new \DateTime('now')));

        return $calendarEvent->getEvents();
    }
}
