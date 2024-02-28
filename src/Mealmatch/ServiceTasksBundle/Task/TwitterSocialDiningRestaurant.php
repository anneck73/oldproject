<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ServiceTasksBundle\Task;

use Doctrine\Common\Collections\ArrayCollection;
use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Mealmatch\ApiBundle\MealMatch\CollectionHelper;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Error\Error;

class TwitterSocialDiningRestaurant extends TwitterTask
{
    public function runWithParameters(ArrayCollection $parameters)
    {
        $this->runParameters = $parameters;
        /** @var string $taskName */
        $taskName = $parameters->get('taskName');

        /** @var TwigEngine $twig */
        $twig = $this->container->get('templating');

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

        // connect using credentials
        $twitter = $this->connect('MealmatchKoeln', $output);

        if ($output->isVerbose()) {
            $output->writeln('Twitter SocialDiningRestaurants');
        }

        $tweets = array();
        $restaurants = array();
        $rMealsC = new ArrayCollection($this->container->get('api.pro_meal.service')->findAllRunning());

        $today = new \DateTime('today');
        $rMealsC = CollectionHelper::filterBaseMealsEqualOrAfterStart($rMealsC, $today);
        /** @var ProMeal $meal */
        foreach ($rMealsC as $meal) {
            $restaurantName = $meal->getHost()->getUsernameCanonical();
            if (!\in_array($restaurantName, $restaurants, true)) {
                $restaurants[] = $restaurantName;
                $output->writeln('rName '.$restaurantName);
                $output->writeln('Status '.$meal->getStatus());
                $city = $meal->getHost()->getRestaurantProfile()->getAddress()->getCity();
                $subLocality = $meal->getHost()->getRestaurantProfile()->getAddress()->getSublocality();
                $minOffer = $meal->getMinOfferPrice();
                $offerID = $meal->getMealOffers()->first()->getId();
                $startDate = $meal->getStartDateTime()->format('d.m.Y h:i');
                $tableTopic = $meal->getTableTopic();
                $currency = $meal->getSharedCostCurrency();

                // Create a Tweet using templates ...
                try {
                    $msg = $twig->render(
                        '@Api/Twitter/SocialDiningRestaurant.html.twig',
                        array(
                            'city' => $city,
                            'restaurantName' => $restaurantName,
                            'minOffer' => $minOffer,
                            'offerID' => $offerID,
                            'startDate' => $startDate,
                            'subLocality' => $subLocality,
                            'tableTopic' => $tableTopic,
                            'mealID' => $meal->getId(),
                            'currency' => $currency,
                        )
                    );
                    $tweets[] = $msg;
                } catch (Error $e) {
                    $output->writeln('ERROR:'.$e->getMessage());
                }
            }
            $output->writeln('Skipped '.$restaurantName);
        }

        // We attach an image :)
        $rootPath = $this->container->getParameter('kernel.project_dir');

        // Send ...
        try {
            foreach ($tweets as $tweet) {
                $rand = random_int(1, 141);
                $imgPath = $rootPath.'/web/bundles/mmwebfront/images/cards/card_'.$rand.'.png';
                $output->writeln('Sending: ');
                $output->writeln($tweet.'('.\strlen($tweet).')');
                // $twitter->send($msg, array($imgPath));
                $this->sendTweet($twitter, $msg, array($imgPath));
                sleep(random_int(1, 3));
            }
        } catch (\TwitterException $e) {
            $output->writeln('ERROR:'.$e->getMessage());

            return;
        }

        $output->writeln(
            'Twitter Social Dining Restaurant done with '.\count($tweets).' tweets!'
        );
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
