<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ServiceTasksBundle\Task;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class TwitterRegistration extends TwitterTask
{
    public function runWithParameters(ArrayCollection $parameters)
    {
        $this->runParameters = $parameters;
        /** @var string $taskName */
        $taskName = $parameters->get('taskName');

        /** @var OutputInterface $output */
        $output = $parameters->get('output');
        $this->output = $output;
        if ($output->isVerbose()) {
            $output->writeln(
                array(
                    'RunWithParameters' => json_encode($parameters->toArray()),
                )
            );
        }
        /** @var TwigEngine $twig */
        $twig = $this->container->get('templating');
        $rootPath = $this->container->getParameter('kernel.project_dir');
        $cardsConfig = Yaml::parseFile($rootPath.'/src/Mealmatch/ApiBundle/Resources/config/MealmatchCards.yml');

        // We select a random card
        $rand = random_int(1, 141);
        $imgPath = $rootPath.'/web/bundles/mmwebfront/images/cards/card_'.$rand.'.png';
        $hashtags = $cardsConfig['card'.$rand]['hashtags'];
        $text = $cardsConfig['card'.$rand]['text'];

        // Create a Tweet using templates ...
        $msg = $twig->render(
            '@Api/Twitter/Registration.html.twig',
            array(
                'text' => $text,
                'hashtags' => $hashtags,
                'cardname' => 'card-'.$rand,
            )
        );

        // Connect to twitter ...
        $twitter = $this->connect('MealmatchKoeln', $output);
        // Send ...
        $this->sendTweet($twitter, $msg, array($imgPath));

        $output->writeln('Twitter Registration DONE');
    }

    public function encodeURI($url)
    {
        // http://php.net/manual/en/function.rawurlencode.php
        // https://developer.mozilla.org/en/JavaScript/Reference/Global_Objects/encodeURI
        $unescaped = array(
            '%2D' => '-', '%5F' => '_', '%2E' => '.', '%21' => '!', '%7E' => '~',
            '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')',
        );
        $reserved = array(
            '%3B' => ';', '%2C' => ',', '%2F' => '/', '%3F' => '?', '%3A' => ':',
            '%40' => '@', '%26' => '&', '%3D' => '=', '%2B' => '+', '%24' => '$',
        );
        $score = array(
            '%23' => '#',
        );

        return strtr(rawurlencode($url), array_merge($reserved, $unescaped, $score));
    }
}
