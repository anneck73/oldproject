<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Controller\Meal;

use Doctrine\ORM\OptimisticLockException;
use Mealmatch\ApiBundle\Controller\ApiController;
use Mealmatch\ApiBundle\Entity\Meal\BaseMeal;
use Mealmatch\ApiBundle\Entity\Meal\MealJoinRequest;
use Mealmatch\ApiBundle\Exceptions\ServiceDataException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Basemeal controller.
 *
 * @Route("api/basemeal")
 * @Security("has_role('ROLE_USER')")
 */
class BaseMealController extends ApiController
{
    /**
     * Join the current user into the specified BaseMeal.
     *
     * @Route("/{id}/join", name="api_basemeal_join")
     * @Method("GET")
     *
     * @param MealJoinRequest|null $joinRequest
     *
     * @throws OptimisticLockException
     * @throws ServiceDataException
     *
     * @return RedirectResponse
     */
    public function joinAction(MealJoinRequest $joinRequest = null): RedirectResponse
    {
        if (null === $joinRequest) {
            throw $this->createNotFoundException('JoinReq not found!');
        }
        $user = $this->getUser();
        $this->get('api.meal.service')->joinMeal($joinRequest, $user);

        return $this->redirectToRoute('joinrequest_index', array('id' => $joinRequest->getBaseMeal()->getId()));
    }
}
