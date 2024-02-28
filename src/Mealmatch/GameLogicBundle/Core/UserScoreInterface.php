<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\GameLogicBundle\Core;

use Mealmatch\GameLogicBundle\User\GameUserInterface;

interface UserScoreInterface extends ScoreInterface
{
    /**
     * @return GameUserInterface
     */
    public function getUser(): GameUserInterface;
}
