<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ServiceTasksBundle\Command;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use MMApiBundle\Entity\Address;
use MMApiBundle\Entity\JoinRequest;
use MMApiBundle\Entity\Meal;
use MMUserBundle\Entity\MMUser;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Error\Error as TwigError;

class MMServiceTaskRunnerCommand extends ContainerAwareCommand
{
    /**
     * Just give it a name ...
     */
    protected function configure()
    {
        $this
            ->setName('mm:service-task-runner')
            ->setDescription('Mealmatch S.T.R. Mark1')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /*
        $this->doUpdateAddress();
        $invalidLocations = $this->doUpdateAddress();
        $output->writeln('UPDATED Address: '.sizeof($invalidLocations));
        */

        $reminders5 = $this->doJoinRequestReminders5Days($output);
        $output->writeln('5DReminders processed: '.$reminders5->count());
        $reminders2 = $this->doJoinRequestReminders2Days();
        $output->writeln('2DReminders processed: '.$reminders2->count());
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
     *
     * @return array|Address[]
     */
    protected function doUpdateAddress(): array
    {
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $invalidLocations = $em->getRepository('MMApiBundle:Address')
                               ->findBy(
                                   array('locationAddress' => '-')
                               )
        ;
        $addressService = $this->getContainer()->get('mm.address');
        /** @var Address $invalidLocation */
        foreach ($invalidLocations as $invalidLocation) {
            /** @var Meal $meal */
            $meal = $invalidLocation->getMeal();

            $addressService->updateAddressLocationFrom($meal);
        }

        return $invalidLocations;
    }

    private function doJoinRequestReminders5Days(OutputInterface $output): ArrayCollection
    {
        $reminders = new ArrayCollection();
        // (1) Get all Meals who start in 5 Days from now
        $now = new DateTime();
        $in5Days = $now->modify('+5 days');

        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $mealsIn5Days = $em->getRepository('MMApiBundle:Meal')->findByStartdate($in5Days);

        // (2) Get all joinRequest in status "accepted"
        /** @var Meal $meal */
        foreach ($mealsIn5Days as $meal) {
            $output->writeln('5DReminders processing MEAL: '.$meal->getTitle());
            $mealJR = $meal->getJoinRequests();
            $output->writeln('Found: '.$mealJR->count()." JR's.");
            /** @var JoinRequest $joinR */
            foreach ($mealJR as $joinR) {
                if ($joinR->isAccepted() && !$joinR->isPayed()) {
                    /** @var MMUser $guest */
                    $guest = $joinR->getCreatedBy();
                    $this->sendReminder($guest, $joinR, '5Day');
                    $output->writeln('5DReminders (+) JRID:'.$joinR->getId());
                }
            }
        }

        $reminders = new ArrayCollection($mealsIn5Days);

        return $reminders;
    }

    /**
     * Sends the JoinRequest reminder for the meal to the guest.
     *
     * @param MMUser      $guest    the guest to receive the email
     * @param JoinRequest $joinReq  the meal this is about
     * @param string      $reminder
     *
     * @throws TwigError
     */
    private function sendReminder(MMUser $guest, JoinRequest $joinReq, $reminder): void
    {
        $twig = $this->getContainer()->get('templating');
        $message = \Swift_Message::newInstance();
        $meal = $joinReq->getMeal();
        $message
            ->setSubject('[Meal Erinnerung] - '.$meal->getTitle())
            ->setFrom('mailer@mealmatch.de')
            ->setTo($guest->getEmail())
            ->setBody(
                $twig->render(
                    '@API/Emails/'.$reminder.'Reminder.html.twig',
                    array(
                        'GUEST' => $guest,
                        'JR' => $joinReq,
                        'MEAL' => $meal,
                        'HOST' => $meal->getHost(),
                    )
                ),
                'text/html'
            )
        ;

        $this->getContainer()->get('swiftmailer.mailer')->send($message);
    }

    private function doJoinRequestReminders2Days(): ArrayCollection
    {
        $reminders = new ArrayCollection();
        // (1) Get all Meals who start in 2 Days from now
        $now = new DateTime();
        $in2Days = $now->modify('+2 days');

        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $mealsIn2Days = $em->getRepository('MMApiBundle:Meal')->findByStartdate($in2Days);

        // (2) Get all joinRequest in status "accepted" -> (not payed yet)
        /** @var Meal $meal */
        foreach ($mealsIn2Days as $meal) {
            $mealJR = $meal->getJoinRequests();
            /** @var JoinRequest $joinR */
            foreach ($mealJR as $joinR) {
                if ($joinR->isAccepted() && !$joinR->isPayed()) {
                    /** @var MMUser $guest */
                    $guest = $joinR->getCreatedBy();
                    $this->sendReminder($guest, $joinR, '2Day');
                }
            }
        }

        $reminders = new ArrayCollection($mealsIn2Days);

        return $reminders;
    }
}
