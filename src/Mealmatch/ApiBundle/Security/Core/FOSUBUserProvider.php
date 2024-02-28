<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Security\Core;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use MMUserBundle\Entity\MMUser;
use MMUserBundle\Services\ImageUploaderService;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * The provider is used to connect the user base with informations from a remote resource.
 *
 * The FOSUBUserProvider extends HWI\FOSUBUserProvider and replace it in all symfony service configurations.
 */
class FOSUBUserProvider extends BaseClass
{
    /** @var ImageUploaderService $imageUploaderService */
    private $imageUploaderService;

    /**
     * Injects the image uploader service to be used during user creations/updates.
     *
     * @param ImageUploaderService $imageUploader the image uploader to use
     */
    public function setImageUploader(ImageUploaderService $imageUploader)
    {
        $this->imageUploaderService = $imageUploader;
    }

    /**
     * {@inheritdoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $property = $this->getProperty($response);
        $username = $response->getUsername();

        //on connect - get the access token and the user ID
        $service = $response->getResourceOwner()->getName();

        $setter = 'set'.ucfirst($service);
        $setter_id = $setter.'Id';
        $setter_token = $setter.'AccessToken';

        /** @var MMUser $previousUser */
        $previousUser = $this->userManager->findUserBy(array($property => $username));
        if (null !== $previousUser) {
            $previousUser->$setter_id(null);
            $previousUser->$setter_token(null);
            $this->userManager->updateUser($previousUser);
        }

        //we connect current user
        $user->$setter_id($username);
        $user->$setter_token($response->getAccessToken());
        $userProfil = $user->getProfile();
        $userProfil->setFirstName($response->getFirstName());
        $userProfil->setLastName($response->getLastName());

        $this->userManager->updateUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        // First some error handling ...
        $responseArray = $response->getResponse();
        if (!empty($responseArray['error'])) {
            throw new \Exception('Error: '.$responseArray['error'].' RESP: '.json_encode($responseArray));
        }
        if (null === $response->getEmail()) {
            throw new \Exception('NoEmail in Response! ');
        }

        // from here on, we have an email and no error! from the response.

        /** @var MMUser $user */
        $user = $this->userManager->findUserByEmail($response->getEmail());

        /** @var string $resourceOwnerName e.g. facebook, google, etc ... */
        $resourceOwnerName = $response->getResourceOwner()->getName();

        // No user found ... let's register ...
        if (null === $user) {
            $service = $response->getResourceOwner()->getName();
            $setter = 'set'.ucfirst($service);
            $setter_id = $setter.'Id';
            $setter_token = $setter.'AccessToken';
            $user = $this->userManager->createUser();

            $user->addRole('ROLE_HOME_USER');

            $profile = $user->getProfile();
            $profile->setFirstName($response->getFirstName());
            $profile->setLastName($response->getLastName());

            if (null !== $response->getProfilePicture()) {
                $imageName = $this->imageUploaderService->uploadImageByUrl(
                    $response->getProfilePicture(),
                    'image/u'.$user->getHash()
                );
                $profile->setImageName($imageName);
            }
            $user->setProfile($profile);
            // @todo: Check and or verify loaded user ....
            // Now connect resource_owner ... e.g. facebook
            $user->$setter_id($response->getUsername());
            $user->$setter_token($response->getAccessToken());
            // Normalize the nickname from facebook
            // @todo: switch own resource owner for specifics!!!
            $user->setUsername(str_replace(' ', '', $response->getNickname()));
            $user->setEmail($response->getEmail());
            // @todo: using hash is unsecure cause hash is a public resource! think about a better way!
            $user->setPassword($user->getHash());
            $user->setEnabled(true);
            $this->userManager->updateUser($user);

            return $user;
        }

        // User was found, lets simply load it using default mechanisms provided by HWI.
        $user = parent::loadUserByOAuthUserResponse($response);
        // @todo: Check and or verify loaded user ....
        // Now overwrite the access token for the specified resourceOwner ...
        $setter = 'set'.ucfirst($resourceOwnerName).'AccessToken';
        $user->$setter($response->getAccessToken());

        return $user;
    }
}
