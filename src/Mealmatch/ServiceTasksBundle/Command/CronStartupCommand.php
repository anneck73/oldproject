<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ServiceTasksBundle\Command;

use Cron\CronBundle\Entity\CronJob;
use Cron\Exception\InvalidPatternException;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Dries De Peuter <dries@nousefreak.be> - Original Author
 * @author Wizard <wizard@mealmatch.de> - Customized for mealmatch
 */
class CronStartupCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('cron:startup')
            ->setDescription('Mealmatch specific cron system startup sequence');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('<info>Mealmatch CRON startup sequence ...</info>');

        // This array DEFINES the cron jobs to be created and started.
        $startupJobs = array(
            array(
                'id' => 1,
                'name' => 'Log_Cleaner',
                'command' => 'mm:log:clean',
                'schedule' => '* */5 * * *',
                'description' => 'Log cleaner!',
            ),
            array(
                'id' => 2,
                'name' => 'Check_Finished_Meals',
                'command' => 'mm:checkfinishedmeals',
                'schedule' => '*/15 * * * *',
                'description' => 'Sets the finished status for meals',
            ),
            // mealmatch:run_command RegistrationReminder
            array(
                'id' => 3,
                'name' => 'Registration_Email_Reminder',
                'command' => 'mealmatch:run_command RegistrationReminder',
                'schedule' => '0 8 * * 1,3,5',
                'description' => 'Is sending reminder emails not activated users.',
            ),
            // mealmatch:run_command TwitterSocialDiningCity Köln
            array(
                'id' => 4,
                'name' => 'TwitterBot_Stadt_Köln',
                'command' => 'mealmatch:run_command TwitterSocialDiningCity Köln',
                'schedule' => '45 7-18 * * 1-5',
                'description' => 'Is sending tweets on twitter.com/MealmatchKoeln',
            ),
            // mealmatch:run_command TwitterSocialDiningRestaurant
            array(
                'id' => 5,
                'name' => 'TwitterBot_Restaurants',
                'command' => 'mealmatch:run_command TwitterSocialDiningRestaurant',
                'schedule' => '15 7-18 * * 1-5',
                'description' => 'Is sending tweets on twitter.com/MealmatchKoeln',
            ),
            // mm:mangopay:payout_promeals
            array(
                'id' => 6,
                'name' => 'Mangopay_PayOUT_ProMeals',
                'command' => 'mm:mangopay:payout_promeals',
                'schedule' => '* */5 * * *',
                'description' => 'Payment to RESTAURANT Bank-Wire',
            ),
            // mm:mangopay:payout_homemeals
            array(
                'id' => 7,
                'name' => 'Mangopay_PayOUT_HomeMeals',
                'command' => 'mm:mangopay:payout_homemeals',
                'schedule' => '* */5 * * *',
                'description' => 'Payment to HOME HOST Bank-Wire',
            ),
        );

        // This loop works through the startupJobs array and processes it accordingly.
        foreach ($startupJobs as $job) {
            $newJob = new CronJob();
            /* @noinspection DisconnectedForeachInstructionInspection */
            $output->writeln('Job-Data: '.json_encode($job));
            // JobName
            $jobName = $job['name'];
            $newJob->setName($jobName);
            $output->writeln('Validating Job-Name: '.$jobName);
            $this->validateJobName($jobName, $output);

            // Command name
            $command = $job['command'];
            $newJob->setCommand($command);
            $output->writeln('Validating Command: '.$command);
            $this->validateCommand($command);

            // Schedule
            $schedule = $job['schedule'];
            $newJob->setSchedule($schedule);
            $output->writeln('Validating Schedule: '.$schedule);
            $this->validateSchedule($schedule);

            // Description
            $newJob->setDescription($job['description']);
            // enable the job
            $newJob->setEnabled(true);

            $this->getContainer()->get('cron.manager')
                ->saveJob($newJob);
            $output->writeln('Saved new JOB from data ID:'.$job['id']);
        }
    }

    /**
     * Validate the job name.
     *
     * @param string          $name
     * @param OutputInterface $output
     *
     * @return string
     */
    protected function validateJobName(string $name, OutputInterface $output): string
    {
        if (!$name || '' === $name) {
            throw new InvalidArgumentException('Please set a name.');
        }

        if ($this->queryJob($name)) {
            $output->writeln('Deleting existing Job-Name: '.$name.'!!!!');
            // During startup we delete existing jobs!
            $cronJobToDelete = $this->queryJob($name);
            /* @noinspection NullPointerExceptionInspection */
            $this->getContainer()->get('cron.manager')->deleteJob($cronJobToDelete);
            // throw new InvalidArgumentException('Name already in use.');
        }

        return $name;
    }

    /**
     * Validate the command.
     *
     * @param string $command
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    protected function validateCommand($command): string
    {
        $parts = explode(' ', $command);
        $this->getApplication()->get((string) $parts[0]);

        return $command;
    }

    /**
     * Validate the schedule.
     *
     * @param string $schedule
     *
     * @throws InvalidPatternException
     * @throws InvalidArgumentException
     *
     * @return string
     */
    protected function validateSchedule($schedule): string
    {
        $this->getContainer()->get('cron.validator')
            ->validate($schedule);

        return $schedule;
    }

    /**
     * @param string $jobName
     *
     * @return CronJob|null
     */
    protected function queryJob($jobName): ?CronJob
    {
        return $this->getContainer()->get('cron.manager')
            ->getJobByName($jobName);
    }
}
