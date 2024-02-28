<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ServiceTasksBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Workflow\Exception\LogicException;

class MealTransitionsCommand extends Command
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
            ->setName('mm:meal:transitions')
            ->setDescription('Executes meal transactions.')
            ->addArgument('mealID', InputArgument::OPTIONAL, 'The ID of the Meal')
            ->addArgument('transition', InputArgument::OPTIONAL, 'The transition to execute.')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description');
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
        $output->writeln('Mealmatch Transition Command!');
        $input->validate();
        $mealID = $input->getArgument('mealID');

        if (null === $mealID) {
            $output->writeln('No mealID!');

            return;
        }
        $transition = $input->getArgument('transition');
        if (null === $transition) {
            $output->writeln('No transition!');

            return;
        }

        $token = new AnonymousToken('DUMMY', 'SYSTEM', array('ROLE_SYSTEM'));
        $this->getContainer()->get('security.token_storage')->setToken($token);

        $workflow = $this->getContainer()->get('workflow.base_meal');
        $meal = $this->getContainer()->get('api.meal.service')->restore($mealID);
        try {
            $workflow->apply($meal, $transition);
            $output->writeln("Executed transtion: '$transition' on meal ID: $mealID.");
        } catch (LogicException $logicException) {
            $output->writeln($logicException->getMessage());
        }
    }
}
