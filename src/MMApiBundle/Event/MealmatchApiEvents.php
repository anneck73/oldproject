<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMApiBundle\Event;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class MealmatchApiEvents does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class MealmatchApiEvents
{
    const JOIN_REQ_CREATED = 'join.request.created';
    const JOIN_REQ_ACCEPTED = 'join.request.accepted';
    const JOIN_REQ_DENIED = 'join.request.denied';

    const MEAL_TICKET_CREATED = 'meal.ticket.created';
    const MEAL_TICKET_PAYED = 'meal.ticket.payed';
}
