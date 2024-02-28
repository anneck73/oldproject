<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ServiceTasksBundle\Command;

use Doctrine\Common\Collections\ArrayCollection;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServiceTaskRunCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mealmatch:run_command')
            ->setDescription('Executes the specified mealmatch service task.')
            ->setHelp('Usage: mealmatch:run_command RegistrationReminder')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the Task to run.')
            ->addArgument('args', InputArgument::IS_ARRAY, 'Arguments for the Task to run.')

        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('ServiceTask RUN, started ...');

        // Validating input ...
        $input->validate();

        $taskName = $input->getArgument('name');
        $taskArgs = $input->getArgument('args');
        if (null === $taskName) {
            $output->writeln('No name specified! You need to ... errr #$%001001DATACORRUPTIONdeTeCtEd ... we lost the signal, sir!');

            return;
        }
        $output->writeln('Running: '.$taskName.' with args: ['.$taskArgs.']');
        // Create the TaskParameters Collection ...
        $taskParams = new ArrayCollection(
            array(
                'taskName' => $taskName, // this will translate into: 'new $taskName($taskParams)'
                'args' => $taskArgs,
                'output' => $output, // this enables the task to write to the command line
            )
        );

        // Run the Task ...
        try {
            // And now... the magic ...
            $mirrorClass = new ReflectionClass('Mealmatch\\ServiceTasksBundle\\Task\\'.$taskName);
            $taskToRun = $mirrorClass->newInstance($this->getContainer());
            $methodToRun = new ReflectionMethod('Mealmatch\\ServiceTasksBundle\\Task\\'.$taskName,
                'runWithParameters');
            $methodToRun->invokeArgs($taskToRun, array($taskParams));
        } catch (\Throwable $throwable) {
            $output->writeln('ERROR: '.$throwable->getMessage());
            $output->writeln('Trace: '.$throwable->getTraceAsString());
        }

        $output->writeln('ServiceTask RUN, finished.');
    }
}
