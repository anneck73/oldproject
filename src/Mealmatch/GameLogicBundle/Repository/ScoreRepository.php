<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\GameLogicBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserInterface as User;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class ScoreRepository does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class ScoreRepository extends EntityRepository
{
    public function findScoresForUser(User $pUSer)
    {
        $queryB = $this->createQueryBuilder('s');
        $queryB->select('s')
               ->where('s.createdBy = :user')
               ->setParameter('user', $pUSer)
        ;

        return $queryB->getQuery()->getResult();
    }
}
