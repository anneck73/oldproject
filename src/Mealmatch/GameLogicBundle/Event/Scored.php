<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\GameLogicBundle\Event;

use Mealmatch\GameLogicBundle\Core\Score;
use Symfony\Component\EventDispatcher\Event;

/**
 * The Scored event is used to "score" something.
 * The specific "type of score", its value and name is to be retrieved useing getScore() with return a Score.
 *
 * Use this event to create login counter:
 *  new Score($user, 'login', 1, Score::COUNTER_TYPE);
 * or points for friend recommendations
 *  new Score($user, 'friend_invite', 1, Score::POINT_TYPE);
 * or meal creations with more value in it ...
 *  new Score($user, 'meal_create', 5, Score::POINT_TYPE);
 */
class Scored extends Event
{
    const USER = 'game.user.scored';

    /**
     * The score to carry in this event.
     *
     * @var Score
     */
    protected $score;

    /**
     * Scored constructor.
     *
     * @param Score $pScore
     */
    public function __construct(Score $pScore)
    {
        $this->score = $pScore;
    }

    /**
     * Returns the score of this event.
     *
     * @return Score the score of this event
     */
    public function getScore(): Score
    {
        return $this->score;
    }
}
