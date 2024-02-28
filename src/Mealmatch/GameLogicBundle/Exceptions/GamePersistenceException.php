<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\GameLogicBundle\Exceptions;

/**
 * GamePersistenceException indicates that something went wrong during persistence ($em->persist($entity)).
 */
class GamePersistenceException extends \Exception
{
}
