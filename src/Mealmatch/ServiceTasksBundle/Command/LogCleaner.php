<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ServiceTasksBundle\Command;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Mealmatch\ApiBundle\Entity\LogEntry;
use Mealmatch\ApiBundle\Entity\Meal\MealTicketTransaction;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LogCleaner extends Command
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /** @var DateTime $lastMonth */
    private $lastMonth;

    /** @var DateTime $yesterday */
    private $yesterday;

    public function __construct(
        EntityManager $entityManager,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->entityManager = $entityManager;
        $this->lastMonth = (new DateTime())->modify('-1 Month');
        $this->yesterday = (new DateTime())->modify('-1 Day');
    }

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
            ->setName('mm:log:clean')
            ->setDescription('Removes log entries older than 1 Month.');
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
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Mealmatch Log cleaner - start.');
        $input->validate();

        $logsCount = $this->cleanupLogEntries();
        $this->entityManager->flush();
        $output->writeln('Cleaned Log: (Logs:'.$logsCount.').');
        $repsCount = $this->cleanupCronReports();
        $this->entityManager->flush();
        $output->writeln('Cleaned Cron: (Logs:'.$repsCount.').');
        $transCount = $this->cleanupMealticketTransactions();
        $this->entityManager->flush();
        $output->writeln('Cleaned Transitions: (Logs:'.$transCount.').');
        $output->writeln(
            'Mealmatch Log cleaner - finished, removed: '.
                '(Logs:'.$logsCount.'|Reps:'.$repsCount.'|Trans:'.$transCount.').');
    }

    protected function cleanupMealticketTransactions(): int
    {
        /** @var Collection $allLogs */
        $allTransC = new ArrayCollection(
            $this->entityManager->getRepository('ApiBundle:Meal\MealTicketTransaction')->findAll()
        );

        /** @var DateTime $lastMonth */
        $lastMonth = $this->lastMonth;

        $filteredTransC = $allTransC->filter(
            function ($trans) use ($lastMonth) {
                /** @var MealTicketTransaction $trans */
                $transDate = $trans->getCreatedAt();

                return $transDate <= $lastMonth;
            }
        );

        foreach ($filteredTransC as $oldTrans) {
            $this->entityManager->remove($oldTrans);
        }

        return $filteredTransC->count();
    }

    protected function cleanupLogEntries(): int
    {
        /** @var Collection $allLogs */
        $allLogsC = new ArrayCollection(
            $this->entityManager->getRepository('ApiBundle:LogEntry')
                ->findBy(array(), array('createdAt' => 'ASC'), 500)
        );

        /** @var DateTime $lastMonth */
        $lastMonth = $this->lastMonth;

        $filteredLogsC = $allLogsC->filter(
            static function ($logEntry) use ($lastMonth) {
                /** @var LogEntry $logEntry */
                $logEntryDate = $logEntry->getCreatedAt();

                return $logEntryDate <= $lastMonth;
            }
        );

        foreach ($filteredLogsC as $logEntry) {
            $this->entityManager->remove($logEntry);
        }

        return $filteredLogsC->count();
    }

    protected function cleanupCronReports(): int
    {
        /** @var Collection $allLogs */
        $allCronReports = new ArrayCollection(
            $this->entityManager->getRepository('CronCronBundle:CronReport')->findAll()
        );

        /** @var DateTime $yesterday */
        $yesterday = $this->yesterday;

        $fileteredCronReps = $allCronReports->filter(
            function ($cronRep) use ($yesterday) {
                /** @var LogEntry $cronRep */
                $cronRepDate = $cronRep->getRunAt();

                return $cronRepDate <= $yesterday;
            }
        );

        foreach ($fileteredCronReps as $logEntry) {
            $this->entityManager->remove($logEntry);
        }

        return $fileteredCronReps->count();
    }
}
