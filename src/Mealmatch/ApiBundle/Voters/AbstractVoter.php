<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Voters;

    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

    abstract class AbstractVoter implements VoterInterface
    {
        abstract protected function supports($attribute, $subject);

        abstract protected function voteOnAttribute($attribute, $subject, TokenInterface $token);
    }
