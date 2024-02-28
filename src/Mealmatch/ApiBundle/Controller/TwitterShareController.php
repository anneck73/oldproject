<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Controller;

use MartinGeorgiev\SocialPost\Provider\Message;
use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Connects to the Mealmatch Twitter APP and posts Meals with.
 *
 * @Route("api/share")
 * @Security("has_role('ROLE_USER')")
 */
class TwitterShareController extends ApiController
{
    /**
     * @Route("/twitter/publish/promeal/{proMeal}", name="api_promeal_publish_twitter")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function publishProMealOnTwitterAction(ProMeal $proMeal)
    {
        $message = new Message('#mealmatch Restaurant-Meal '.$proMeal->getTableTopic()
            .' '.$this->generateUrl('public_promeal_show', array('id' => $proMeal->getId())
            )
        );

        $response = $this->get('social_post')->publish($message);

        return $this->redirectToRoute('api_homemeal_index');
    }

    /**
     * @Route("/facebook/publish/promeal/{proMeal}", name="api_promeal_publish_twitter")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function publishProMealOnFacebookAction(ProMeal $proMeal)
    {
        $message = new Message('#mealmatch Restaurant-Meal '.$proMeal->getTableTopic()
            .' '.$this->generateUrl('public_promeal_show', array('id' => $proMeal->getId())
            )
        );

        $response = $this->get('social_post')->publish($message);

        return $this->redirectToRoute('api_homemeal_index');
    }
}
