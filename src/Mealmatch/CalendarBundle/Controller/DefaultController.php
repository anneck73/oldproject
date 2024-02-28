<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\CalendarBundle\Controller;

use AncaRebeca\FullCalendarBundle\Event\CalendarEvent;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("index", name="default")
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('MealmatchCalendarBundle:Default:index.html.twig');
    }

    /**
     * @see http://fullcalendar.io/docs/event_data/events_json_feed/
     *
     *
     * @Route("loadCalendar2", options={"expose"=true}, name="mm_calendar_loader2")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function loadAction(Request $request)
    {
        $startDate = new \DateTime('now');
        $endDate = new \DateTime('now');
        $filters = $request->get('filters', array());

        try {
            $content = $this
                ->get('anca_rebeca_full_calendar.service.calendar')
                ->getData($startDate, $endDate, $filters);
            $status = empty($content) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;
        } catch (\Exception $exception) {
            $content = json_encode(array('error' => $exception->getMessage()));
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($content);
        $response->setStatusCode($status);

        return $response;
    }

    /**
     * Dispatch a CalendarEvent and return a JSON Response of any events returned.
     *
     * @param Request $request
     *
     * @Route("loadCalendar", options={"expose"=true}, name="mm_calendar_loader")
     *
     * @return Response
     */
    public function loadCalendarAction(Request $request)
    {
        $startDatetime = new \DateTime('now');
        // $startDatetime->setTimestamp($request->get('start'));

        $endDatetime = new \DateTime('now');
        // $endDatetime->format();

        /** @var ArrayCollection $events */
        $events = $this->container->get(
            'event_dispatcher')->dispatch(
                CalendarEvent::SET_DATA,
                new CalendarEvent($startDatetime, $endDatetime, $request)
        )->getEvents();

        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->headers->set('Content-Type', 'application/json');

        $return_events = array('Calendar' => 'FooBar');
        foreach ($events as $event) {
            $return_events[] = $event->toArray();
        }

        $response->setContent(json_encode($return_events));

        return $response;
    }
}
