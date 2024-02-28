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
use Symfony\Component\Workflow\Workflow;

class ProcessRedeemerCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('mm:process_redeemer')
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
            // ...
        }
        */
        $output->writeln('Processing Mealtickets with coupon_code request.');

        // We need to "login" with a user cause we write emails/system-messages ...
        $token = new AnonymousToken('DUMMY', 'SYSTEM', array('ROLE_SYSTEM'));
        $this->getContainer()->get('security.token_storage')->setToken($token);

        /** @var Workflow $mealTicketWorkflow */
        $mealTicketWorkflow = $this->getContainer()->get('workflow.meal_ticket');

        $this->getContainer()->get('api.coupon.service');

        $output->writeln('Command result.');
    }
}
