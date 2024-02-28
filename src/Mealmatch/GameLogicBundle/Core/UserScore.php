<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\GameLogicBundle\Core;

use FOS\UserBundle\Model\User;
use Mealmatch\GameLogicBundle\Exceptions\GameException;
use Mealmatch\GameLogicBundle\User\GameUserInterface;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class UserScore does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class UserScore extends Score
{
    /**
     * The User that gains the score.
     *
     * @var User
     */
    private $user;

    public function __construct(User $pUser, int $pValue, string $pName = '-', $pType = Score::POINT_TYPE)
    {
        if (!$pUser instanceof GameUserInterface) {
            throw new GameException('The GameLogicBundle:UserScore requires a GameUser to be created!');
        }
        parent::__construct($pValue, $pName, $pType);
        $this->user = $pUser;
    }

    /**
     * UserScore constructor.
     *
     * @param GameUserInterface $pUser
     * @param mixed             $pType
     */
    public function createByGameUser(
        GameUserInterface $pUser,
        int $pValue,
        string $pName = '-',
        $pType = Score::POINT_TYPE
    ) {
        parent::__construct($pValue, $pName, $pType);
        $this->user = $pUser;
    }

    /**
     * @return GameUserInterface
     */
    public function getUser(): GameUserInterface
    {
        return $this->user;
    }
}
