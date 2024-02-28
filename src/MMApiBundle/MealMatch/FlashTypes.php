<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMApiBundle\MealMatch;

/**
 * This class only holds constants for Flash UI messages.
 * The "values" match the twbs3 "flash" type modals.
 */
class FlashTypes
{
    public static $SUCCESS = 'success';
    public static $WARNING = 'warning';
    public static $INFO = 'info';
    public static $DISMISSABLE = 'warning';
    public static $DANGER = 'danger';
}
