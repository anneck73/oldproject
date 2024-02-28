<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ServiceTasksBundle\Command;

use FOS\UserBundle\Model\UserInterface;
use MMUserBundle\Entity\MMRestaurantProfile;
use MMUserBundle\Entity\MMUser;
use MMUserBundle\Entity\MMUserProfile;
use MMUserBundle\Entity\MMUserSettings;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MmUpdateUserProfilesCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('mm:update-user-profiles')
            ->setDescription('...')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /*
        $argument = $input->getArgument('argument');

        if ($input->getOption('option')) {
            // no options
        }*/

        $userManager = $this->getContainer()->get('api.user_manager');
        $allUsers = $userManager->findUsers();
        /** @var UserInterface $user */
        foreach ($allUsers as $user) {
            if ($user instanceof MMUser) {
                if (null === $user->getSettings()) {
                    $updateUser = $this->addSettings($user);
                    $userManager->updateUser($updateUser);
                    $output->writeln("Updated $user added Settings!");
                }

                if (null === $user->getProfile()) {
                    $updateUser = $this->addUserProfile($user);
                    $userManager->updateUser($updateUser);
                    $output->writeln("Updated $user added Profile!");
                }
                if (null === $user->getRestaurantProfile()) {
                    $updateUser = $this->addRestaurantProfile($user);
                    $userManager->updateUser($updateUser);
                    $output->writeln("Updated $user added RestaurantProfile!");
                }
            }
        }

        $output->writeln($this->getName().' finished running!');
    }

    private function addSettings(MMUser $user)
    {
        $userSettings = new MMUserSettings();
        $user->setSettings($userSettings);

        return $user;
    }

    private function addUserProfile(MMUser $user)
    {
        $userProfile = new MMUserProfile();
        $user->setProfile($userProfile);

        return $user;
    }

    private function addRestaurantProfile(MMUser $user)
    {
        $restProfile = new MMRestaurantProfile();
        $user->setRestaurantProfile($restProfile);

        return $user;
    }
}
