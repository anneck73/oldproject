<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MMProfileImage.
 *
 * @ORM\Table(name="m_m_profile_image")
 * @ORM\Entity(repositoryClass="MMUserBundle\Repository\MMProfileImageRepository")
 */
class MMProfileImage extends MMUploadableImage
{
}
