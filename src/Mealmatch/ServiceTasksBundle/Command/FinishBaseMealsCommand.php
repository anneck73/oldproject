<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ServiceTasksBundle\Command;

use Mealmatch\ApiBundle\Entity\Meal\BaseMeal;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\Workflow;

class FinishBaseMealsCommand extends Command
{
    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     */
    protected function configure()
    {
        $this
            ->setName('mm:checkfinishedmeals')
            ->setDescription('Marks meals as FINISHED.');
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $myArgument with a *description* of this argument, these may also
     *                                    span multiple lines
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Mealmatch Finish BaseMeals Command');
        $input->validate();

        // We need to "login" with a user cause we write emails/system-messages ...
        $token = new AnonymousToken('DUMMY', 'SYSTEM', array('ROLE_SYSTEM'));
        $this->getContainer()->get('security.token_storage')->setToken($token);

        /** @var Workflow $workflow */
        $workflow = $this->getContainer()->get('workflow.base_meal');

        $finishedMeals = $this->getContainer()->get('api.meal.service')->findFinishedMeals();
        $yesterday = (new \DateTime('-1 day'));
        $yesterdayString = $yesterday->format('d.m.Y H:i:s');

        /** @var BaseMeal $meal */
        foreach ($finishedMeals as $meal) {
            $mealID = $meal->getId();
            try {
                $output->writeln(
                    "Meal($mealID:".$meal->getTitle().':'.$meal->getMealEvent()->getId(
                    ).') startDate: '.$meal->getMealEvent()->
                    getStartDateTime()->format('d.m.Y H:i:s').' <-?-> '.$yesterdayString
                );
                if ($meal->getMealEvent()->getStartDateTime() < $yesterday) {
                    $marking = $workflow->apply($meal, 'finish_meal');
                    $markings = implode('|', $marking->getPlaces());
                    $output->writeln("Executed transition finish_meal on meal ID: $mealID -> $markings");
                }
            } catch (LogicException $logicException) {
                $output->writeln($logicException->getMessage());
            }
        }

        $output->writeln('Executed transition finish_meal on '.$finishedMeals->count());
    }
}
