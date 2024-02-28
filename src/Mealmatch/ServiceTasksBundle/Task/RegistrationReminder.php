<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ServiceTasksBundle\Task;

use Doctrine\Common\Collections\ArrayCollection;
use MMUserBundle\Entity\MMUser;
use Swift_Message;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Console\Output\OutputInterface;
use Twitter;

class RegistrationReminder extends DefaultTask
{
    public function runWithParameters(ArrayCollection $parameters)
    {
        /** @var string $taskName */
        $taskName = $parameters->get('taskName');

        /** @var OutputInterface $output */
        $output = $parameters->get('output');
        if ($output->isVerbose()) {
            $output->writeln(
                array(
                    'RunWithParameters' => json_encode($parameters->toArray()),
                )
            );
        }
        // Using a Service to finde the unfished registrations!
        $foundUsers = $this->container->get('api.user_manager')->findUnfinishedRegistrations();

        if ($output->isVerbose()) {
            $output->writeln(array(
                'Unfinished Registrations' => json_encode($foundUsers->toArray()),
            ));
        }

        // TWIG is required for the email below ...
        /** @var TwigEngine $twig */
        $twig = $this->container->get('templating');

        /** @var MMUser $user */
        foreach ($foundUsers as $user) {
            $message = Swift_Message::newInstance();
            $message->setSubject('Erinnerung an deine Registrierung')
                    ->setFrom('mailer@mealmatch.de')
                    ->setTo($user->getEmail())
                    ->setBody(
                        $twig->render(
                            '@Api/Emails/RegistrationReminder.html.twig',
                            array(
                                'user' => $user,
                            )
                        ),
                        'text/html'
                    )
            ;
            $this->sendMail($message);
            $output->writeln('Reminder send to '.$user->getEmail());
        }

        if (0 === $foundUsers->count()) {
            $output->writeln('Found no one to remind! Great News!');
        }
    }

    /**
     * Connects to Twitter using the specified twitterAccount.
     *
     * @param string          $twitterAccount
     * @param OutputInterface $output
     *
     * @return Twitter
     */
    protected function connect(string $twitterAccount, OutputInterface $output): Twitter
    {
        // Get Task specific values ...
        $consumerKey = $this->container->getParameter('twitter_credentials')[$twitterAccount]['consumerKey'];
        $consumerSecret = $this->container->getParameter('twitter_credentials')[$twitterAccount]['consumerSecret'];
        $accessToken = $this->container->getParameter('twitter_credentials')[$twitterAccount]['accessToken'];
        $accessTokenSecret = $this->container->getParameter(
            'twitter_credentials'
        )[$twitterAccount]['accessTokenSecret'];

        // Connect to twitter ...
        try {
            $twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
        } catch (\TwitterException $e) {
            $output->writeln('ERROR:'.$e->getMessage());
        }

        return $twitter;
    }
}
