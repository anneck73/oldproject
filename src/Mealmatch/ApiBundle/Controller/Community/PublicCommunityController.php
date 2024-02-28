<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Community\Controller;

use Mealmatch\ApiBundle\Entity\Community\Community;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PublicCommunityController.
 */
class PublicCommunityController extends Controller
{
    /**
     * @Route("c/{id}/public")
     *
     * @param Community $community
     *
     * @return Response
     */
    public function indexAction(Community $community): Response
    {
        return $this->render('@CUI/index.html.twig', array('name' => $community->getName()));
    }

    /**
     * @Route("c/{id}/member")
     * @IsGranted("COMMUNITY_MEMBER", subject="community")
     *
     * @param Community $community
     *
     * @return Response
     */
    public function memberAction(Community $community): Response
    {
        return $this->render('@CUI/index.html.twig', array('name' => $community->getName()));
    }
}
