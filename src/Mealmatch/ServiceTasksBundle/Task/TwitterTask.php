<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ServiceTasksBundle\Task;

use Symfony\Component\Console\Output\OutputInterface;
use Twitter;

/**
 * Abstract class for Twitter Specifics.
 *
 * @example
 *
 * $twitter = $this->connect();
 * $this->>sendTweet($twitter, 'Test', array('/tmp/img.png'));
 */
abstract class TwitterTask extends DefaultTask
{
    /**
     * Sends a tweet using $Twitter.
     *
     * @param Twitter $twitter the TwitterConnection to use
     * @param string  $msg     a string containing the tweet
     * @param array   $images  an array of paths to images
     */
    protected function sendTweet(Twitter $twitter, string $msg, array $images)
    {
        // Send ...
        try {
            $twitter->send($msg, $images);
            $this->output->writeln('Sending: ');
            $this->output->writeln($msg.' '.json_encode($images));
        } catch (\TwitterException $twitterException) {
            $this->output->writeln('ERROR: '.$twitterException->getMessage());
        }
    }

    /**
     * Connects to Twitter using the specified twitterAccount of the twitter account configuration in symfony parameters.
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
