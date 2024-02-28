<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMApiBundle\Repository;

/**
 * MealTicketRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MealTicketRepository extends \Doctrine\ORM\EntityRepository
{
    public function findByTitle(string $pTitle)
    {
        $queryBuilder = $this->createQueryBuilder('meal_ticket');
        $queryBuilder->where('meal_ticket.titel = :title');
        $queryBuilder->setParameter('title', $pTitle);

        return $queryBuilder->getQuery()->getResult();
    }
}
