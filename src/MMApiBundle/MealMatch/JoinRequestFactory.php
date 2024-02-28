<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMApiBundle\MealMatch;

use Doctrine\ORM\EntityManager;
use MMApiBundle\Entity\JoinRequest;

class JoinRequestFactory
{
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function create()
    {
        $entity = new JoinRequest();
        $this->em->persist($entity);
        $this->em->flush();
        $joinRequstBO = new JoinRequestBO($entity);

        return $joinRequstBO;
    }
}
