<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ServiceTasksBundle\Command;

use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
use Mealmatch\ApiBundle\Exceptions\MealmatchException;
use Mealmatch\ApiBundle\Services\MealTicketService;
use Mealmatch\ApiBundle\Services\RestaurantService;
use Mealmatch\MangopayBundle\Services\MealticketTransactionService;
use Mealmatch\MangopayBundle\Services\PublicMangopayService;
use Mealmatch\ServiceTasksBundle\Services\PayoutProMealsService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MangopayPayoutProMealsCommand extends Command
{
    /**
     * @var PayoutProMealsService
     */
    private $payoutProMealsService;
    /**
     * @var MealTicketService
     */
    private $mealTicketService;
    /**
     * @var MealticketTransactionService
     */
    private $mealticketTransactionService;
    /**
     * @var PublicMangopayService
     */
    private $mangopayService;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var RestaurantService
     */
    private $restaurantService;

    public function __construct(
        LoggerInterface $logger,
        PayoutProMealsService $payoutProMealsService,
        MealTicketService $mealTicketService,
        MealticketTransactionService $mealticketTransactionService,
        PublicMangopayService $mangopayService,
        RestaurantService $restaurantService,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->payoutProMealsService = $payoutProMealsService;
        $this->mealTicketService = $mealTicketService;
        $this->mealticketTransactionService = $mealticketTransactionService;
        $this->mangopayService = $mangopayService;
        $this->logger = $logger;
        $this->restaurantService = $restaurantService;
    }

    /**
     * Configure this command, e.g. self-configuration always called from parent class constructor.
     */
    protected function configure(): void
    {
        $this
            ->setName('mm:mangopay:payout_promeals')
            ->setDescription('Executes the Payout to Host Wallet.');
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
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Mealmatch Mangopay Payout ProMeals Command (started)');
        $input->validate();
        $resultsCollection = $this->payoutProMealsService->findProMealPayouts();
        $count = 1;
        /** @var BaseMealTicket $result */
        foreach ($resultsCollection as $result) {
            $this->logger->debug('---------------------');
            $this->logger->debug('Result #'.$count.json_encode($result));
            $this->logger->debug('>>>>');
            /** @var BaseMealTicket $mealticket */
            $mealticket = $result['ticket'];
            // Determine if the Mealticket needs to be processed, because it did not get a payout yet and it needs one.
            if ($result['isDue']) {
                // the mealticket payout to host isDue ...
                $this->logger->debug('Processing>>:'.$mealticket->getNumber());
                $payInResourceId = $this->mealTicketService->getResourceIdByMangopayObj($mealticket, 'PayIn');
                $payOutResourceId = $this->mealTicketService->getResourceIdByMangopayObj($mealticket, 'PayOut');
                $host = $mealticket->getHost();
                $hostPaymentProfile = $host->getPaymentProfile();
                $valid = $this->restaurantService->isPaymentProfilePayoutValid($hostPaymentProfile);
                if (!$valid) {
                    $this->logger->debug('Skip>>Restaurant Payment Profile Not Valid>>>>'.$mealticket->getNumber());
                    $this->logger->debug('Restaurant of Host: '.$host->getUsername().' is not valid for Payout!');
                    // go to the next $result
                    continue;
                }

                if (null !== $payInResourceId) {
                    // we have a PayIn/SUCCEEDED on that Mealticket, that isDue
                    $this->logger->debug('Processing>>>>'.$mealticket->getNumber());
                    if (null === $payOutResourceId) {
                        // and NO Payout/SUCCEEDED !!! yet ...
                        $this->logger->debug('Processing>1>>>'.$mealticket->getNumber());
                        // Start a PayoutProcess for this ticket ...
                        // Transfer from GuestWallet to HostWallet
                        try {
                            $this->executeGuestToHostWalletTransfer($output, $mealticket);
                            $this->logger->debug('Processing>>2>>>'.$mealticket->getNumber());
                            // PayOut from HostWallet to Host Bankwire
                            $this->executeToHostBankwirePayOut($output, $mealticket);
                            $this->logger->debug('Processing>>>3>>>'.$mealticket->getNumber());
                        } catch (MealmatchException $mealmatchException) {
                            $this->logger->error('<<<<<<<< ERROR: '.$mealmatchException->getMessage());
                        }
                        // Wait ... dont spam mangopay!
                        sleep(1);
                    } else {
                        $this->logger->debug('Skip>>>PayOut Exists!>>>>'.$mealticket->getNumber());
                    }
                } else {
                    $this->logger->debug('Skip>>>NoPayIN>>>>'.$mealticket->getNumber());
                }
            } else {
                $this->logger->debug('Skip>>>NotDueYet>>>>'.$mealticket->getNumber());
            }
            ++$count;
        }

        $this->logger->debug('Processed: '.$count.' Mealtickets.');
        $output->writeln('Mealmatch Mangopay Payout ProMeals Command (finished:'.$count.')');
    }

    /**
     * @param OutputInterface $output
     * @param BaseMealTicket  $mealticket
     *
     * @throws MealmatchException
     */
    protected function executeGuestToHostWalletTransfer(OutputInterface $output, BaseMealTicket $mealticket): void
    {
        $this->logger->debug('>>>>>MT ('.$mealticket->getNumber().')');
        $transfer = $this->mangopayService->createTransferGuestToHostWallet($mealticket);
        $this->logger->debug('>>>>>createdTransfer ('.json_encode($transfer).')');

        $transferResult = $this->mangopayService->executeTransfer($transfer);
        $this->logger->debug('>>>>>transferResults ('.json_encode($transferResult).')');
        $mtt = $this->mealticketTransactionService->createFromTransfer($mealticket, $transferResult);
        $this->logger->debug('>>>>>mttCreated . '.$mtt->getMangoEvent());
    }

    /**
     * @param OutputInterface $output
     * @param BaseMealTicket  $mealticket
     */
    protected function executeToHostBankwirePayOut(OutputInterface $output, BaseMealTicket $mealticket): void
    {
        try {
            $payout = $this->mangopayService->getMangopayPayOutService()->createPayOutToHostBankwire($mealticket);
            $this->logger->debug('>>>>>createdPayout ('.json_encode($payout).')');
        } catch (MealmatchException $mealmatchException) {
            $this->logger->error('<<<<<ERROR processing ('
                .$mealticket->getNumber().
                '): '.$mealmatchException->getMessage());

            return;
        }

        try {
            $payoutResult = $this->mangopayService->getMangopayPayOutService()->doCreatePayOut($payout);
            $this->logger->debug('>>>>>payoutResults ('.json_encode($payoutResult).')');
        } catch (MealmatchException $mealmatchException) {
            $this->logger->error('<<<<<ERROR processing ('
                .$mealticket->getNumber().
                '): '.$mealmatchException->getMessage());

            return;
        }

        try {
            $this->mealticketTransactionService->createFromPayout($mealticket, $payoutResult);
        } catch (MealmatchException $mealmatchException) {
            $this->logger->error('<<<<<ERROR processing ('
                .$mealticket->getNumber().
                '): '.$mealmatchException->getMessage());

            return;
        }
    }
}
