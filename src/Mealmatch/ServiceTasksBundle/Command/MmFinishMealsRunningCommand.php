<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ServiceTasksBundle\Command;

use DateTime;
use Mealmatch\GameLogicBundle\Core\Score;
use Mealmatch\GameLogicBundle\Core\UserScore;
use Mealmatch\GameLogicBundle\Event\Scored;
use MMApiBundle\Entity\Meal;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MmFinishMealsRunningCommand extends ContainerAwareCommand
{
    protected function configure(): void
    {
        $this
            ->setName('mm:finish-meals-running')
            ->setDescription('...')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /** @var DateTime $sixHoursAgo */
        $sixHoursAgo = new \DateTime('now');
        $sixHoursAgo = $sixHoursAgo->modify('-6 hours');
        $query = $em->createQuery(
            'SELECT m
                FROM
                  MMApiBundle:Meal m
                WHERE
                  m.status like \'%RUNNING%\'
                AND
                  m.startDateTime < :pastSixHours'
        )->setParameter('pastSixHours', $sixHoursAgo)
        ;

        $meals = $query->getResult();
        $output->writeln('pastSixHourMark: '.$sixHoursAgo->format('d.m.Y H:m'));
        $mealsFinished = 0;

        /** @var Meal $meal */
        foreach ($meals as $meal) {
            $meal->setStatus(Meal::$STATUS_FINISHED);
            ++$mealsFinished;

            // Create a score for it ...
            $scoredEvent = new Scored(new UserScore($meal->getHost(), 1, 'MealHosted', Score::COUNTER_TYPE));
            $this->getContainer()->get('event_dispatcher')->dispatch(Scored::USER, $scoredEvent);
        }
        $em->flush();

        $output->writeln('FINISHED: '.$mealsFinished);
    }
}
