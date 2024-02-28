<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\ORM\QueryBuilder;
use MangoPay\EventType;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Entity\Meal\BaseMeal;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
use Mealmatch\ApiBundle\Entity\Meal\HomeMeal;
use Mealmatch\ApiBundle\Entity\Meal\MealJoinRequest;
use Mealmatch\ApiBundle\Entity\Meal\MealOffer;
use Mealmatch\ApiBundle\Entity\Meal\MealTicketTransaction;
use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Mealmatch\ApiBundle\Exceptions\MealTicketException;
use MMUserBundle\Entity\MMUser;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Workflow\Workflow;

/**
 * The MealTicket Service creates BaseMealTickets from HomeMeals and ProMeals.
 * It uses container parameters ('mm_fee_promeal', 'mm_fee_homemeal') to calculate the Mealmatch Fee for
 * BaseMealTickets.
 *
 * @see BaseMealTicket
 *
 * @property  container
 */
class MealTicketService implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var EntityManager $entityManager */
    private $entityManager;

    /** @var Logger $logger */
    private $logger;

    /** @var Workflow $workflow */
    private $workflow;

    /** @var JoinRequestService $joinRequest */
    private $joinRequest;

    /**
     * MealTicketService constructor.
     *
     * @param EntityManager      $entityManager
     * @param Logger             $logger
     * @param Workflow           $workflow
     * @param JoinRequestService $joinRequest
     */
    public function __construct(
        EntityManager $entityManager,
        Logger $logger,
        Workflow $workflow,
        JoinRequestService $joinRequest
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->workflow = $workflow;
        $this->joinRequest = $joinRequest;
    }

    /**
     * Returns true if a BaseMealTicket exists for the User + ProMeal + MealOffer.
     *
     * @param MMUser    $guest     the guest for the meal
     * @param ProMeal   $proMeal   the selected promeal
     * @param MealOffer $mealOffer the selected mealoffer
     *
     * @return bool
     */
    public function hasProMealTicket(MMUser $guest, ProMeal $proMeal, MealOffer $mealOffer): bool
    {
        $mealTicket = $this->entityManager->getRepository('ApiBundle:Meal\BaseMealTicket')
                                          ->findOneBy(
                                              array(
                                                  'guest' => $guest,
                                                  'baseMeal' => $proMeal,
                                                  'selectedMealOffer' => $mealOffer,
                                              )
                                          )
        ;

        $this->logger->addInfo(
            sprintf(
                'Has ProMealTicket:%s-MealOffer:%s-Guest:%s -> %s',
                $proMeal->getId(),
                $mealOffer->getId(),
                $guest->getId(),
                $mealTicket
            )
        );

        return null !== $mealTicket;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param MMUser    $guest
     * @param ProMeal   $proMeal
     * @param MealOffer $mealOffer
     *
     * @return BaseMealTicket
     */
    public function getProMealTicket(MMUser $guest, ProMeal $proMeal, MealOffer $mealOffer): BaseMealTicket
    {
        return $this->entityManager->getRepository('ApiBundle:Meal\BaseMealTicket')
                                   ->findOneBy(
                                       array(
                                           'guest' => $guest,
                                           'baseMeal' => $proMeal,
                                           'selectedMealOffer' => $mealOffer,
                                       )
                                   )
            ;
    }

    public function hasHomeMealTicket(MealJoinRequest $joinRequest): bool
    {
        $mealTicket = $this->entityManager->getRepository('ApiBundle:Meal\BaseMealTicket')
                                          ->findBy(
                                              array(
                                                  'baseMeal' => $joinRequest->getBaseMeal(),
                                                  'guest' => $joinRequest->getCreatedBy(),
                                              )
                                          )
        ;
        if (0 === \count($mealTicket)) {
            $this->logger->addInfo(
                'Guest '.$joinRequest->getCreatedBy().
                ' has no MealTicket for Meal: '.$joinRequest->getBaseMeal()->getTitle()
            );

            return false;
        }
        $this->logger->addInfo(
            'Guest '.$joinRequest->getCreatedBy().
            ' already has a MealTicket for Meal: '.$joinRequest->getBaseMeal()->getTitle()
        );

        return true;
    }

    public function hasHomeMealTicketFrom($guest, $homeMeal): bool
    {
        $mealTicket = $this->entityManager->getRepository('ApiBundle:Meal\BaseMealTicket')
                                          ->findOneBy(
                                              array(
                                                  'guest' => $guest,
                                                  'baseMeal' => $homeMeal,
                                              )
                                          )
        ;

        return null !== $mealTicket;
    }

    public function getHomeMealTicket(MMUser $guest, HomeMeal $homeMeal): BaseMealTicket
    {
        return $this->entityManager->getRepository('ApiBundle:Meal\BaseMealTicket')
                                   ->findOneBy(
                                       array(
                                           'guest' => $guest,
                                           'baseMeal' => $homeMeal,
                                       )
                                   )
            ;
    }

    /**
     * Restores a BaseMealTicket by ID.
     *
     * @param int $id
     *
     * @throws MealTicketException if the BaseMealTicket is not found
     *
     * @return BaseMealTicket the BaseMealTicket found
     */
    public function restore(int $id): BaseMealTicket
    {
        $mealTicket = $this->entityManager->getRepository('ApiBundle:Meal\BaseMealTicket')->find($id);

        if (null === $mealTicket) {
            $this->logger->addError('MealTicket mit id '.$id.' nicht gefunden!');
            throw new MealTicketException('MealTicket mit id '.$id.' nicht gefunden!');
        }

        return $mealTicket;
    }

    /**
     * Updates (persist and flush) the BaseMealTicket.
     *
     * @param BaseMealTicket $mealTicket
     *
     * @throws MealTicketException
     *
     * @return BaseMealTicket
     */
    public function update(BaseMealTicket $mealTicket): BaseMealTicket
    {
        return $this->persistAndFlush($mealTicket);
    }

    /**
     * Tries to find the a BaseMealTicket for.
     *
     * @param HomeMeal $meal
     * @param MMUser   $guest
     * @param int      $extraGuests
     *
     * @throws MealTicketException
     *
     * @return BaseMealTicket
     */
    public function findOrCreateNewFromHomeMeal(HomeMeal $meal, MMuser $guest): BaseMealTicket
    {
        // @todo: re-write this to use the workflow and not the exception to controll the flow of the app!!!
        try {
            // find it and return, or throw an exception!
            return $this->findOneByMealAndUser($meal, $guest);
        } catch (NoResultException $noResultException) {
            // no Result ... create a new BaseMealTicket
            return $this->createFromHomeMeal($meal, $guest);
        } catch (NonUniqueResultException $nonUniqueResultException) {
            $this->logger->critical('Found more than 1 results searching for BaseMealTicket!');
            throw new MealTicketException('!!!CRITICAL MORE THAN ONE MEALTICKET FOUND!!!');
        }
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param ProMeal   $meal
     * @param MealOffer $mealOffer
     * @param MMUser    $guest
     * @param int       $extraGuests
     * @param string    $myArgument  with a *description* of this argument, these may also
     *                               span multiple lines
     *
     * @throws MealTicketException
     *
     * @return BaseMealTicket
     */
    public function findOrCreateNewFromProMeal(
        ProMeal $meal,
        MealOffer $mealOffer,
        MMuser $guest
    ): BaseMealTicket {
        //@todo: rebuild to use workflow: $this->workflow->apply($mealTicket, 'create_ticket');
        try {
            return $this->findOneByMealUserOffer($meal, $guest, $mealOffer);
        } catch (NoResultException $noResultException) {
            return $this->createNewFromProMeal($meal, $mealOffer, $guest);
        } catch (NonUniqueResultException $nonUniqueResultException) {
            $this->logger->critical('Found more than 1 results searching for BaseMealTicket!');
            throw new MealTicketException('!!!CRITICAL MORE THAN ONE MEALTICKET FOUND!!!');
        }
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param BaseMealTicket $mealTicket
     * @param string         $myArgument with a *description* of this argument, these may also
     *                                   span multiple lines
     *
     * @throws OptimisticLockException
     */
    public function delete(BaseMealTicket $mealTicket)
    {
        $this->entityManager->remove($mealTicket);
        $this->entityManager->flush();
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param BaseMealTicket $mealTicket
     * @param MMUser         $user
     * @param string         $myArgument with a *description* of this argument, these may also
     *                                   span multiple lines
     *
     * @throws MealTicketException
     *
     * @return bool
     */
    public function validateOwnership(BaseMealTicket $mealTicket, MMUser $user): bool
    {
        if ($mealTicket->getCreatedBy() !== $user && !$user->hasRole('ROLE_ADMIN')) {
            throw new MealTicketException(
                'Permission denied! You are not the owner of the ticket! '
                .$mealTicket->getCreatedBy().'<=>'.$user
            );
        }

        return true;
    }

    /**
     * Counts the number of "tries" the creator of the MealTicket tried to execute the payment.
     * Example: The user clicks on "cancle" during PayPal->doPayment();.
     *
     *
     * @return int
     */
    public function getPaymentTries(BaseMealTicket $mealTicket): int
    {
        // @todo: this is too simple ... or not ?
        return $mealTicket->getTransactions()->count();
    }

    /**
     * Searches for a matching MealTicket and returns one!.
     *
     * If the search results is empty or non unique an exception is thrown!
     *
     * @param BaseMeal $meal
     * @param MMUser   $user
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     *
     * @return BaseMealTicket
     */
    public function findOneByMealAndUser(BaseMeal $meal, MMUser $user): BaseMealTicket
    {
        /** @var QueryBuilder $qb */
        $qb = $this->entityManager->getRepository('ApiBundle:Meal\BaseMealTicket')->createQueryBuilder('mt')
                                  ->select('mt')
                                  ->join('mt.guest', 'guest')
                                  ->join('mt.baseMeal', 'meal')
                                  ->andWhere('guest.id = :guestID')
                                  ->setParameter('guestID', $user->getId())
                                  ->andWhere('meal.id = :mealID')
                                  ->setParameter('mealID', $meal->getId())
        ;

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param BaseMeal $meal
     * @param MMUser   $user
     *
     * @return mixed
     */
    public function findAllByMealAndUser(BaseMeal $meal, MMUser $user)
    {
        $result = $this->entityManager->getRepository('ApiBundle:Meal\BaseMealTicket')
                                   ->findBy(
                                       array(
                                           'guest' => $user,
                                           'baseMeal' => $meal,
                                           'status' => ApiConstants::MEAL_TICKET_STATUS_PAYED,
                                       )
                                   )
            ;

        if (null === $result) {
            $count = 0;
        } else {
            $count = \count($result);
        }

        $this->logger->addDebug(
            sprintf(
                'MealTicketService:findAllByMealAndUser(%s, %s):%s',
                $meal,
                $user,
                $count
            )
        );

        return $result;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param HomeMeal $meal
     * @param MMUser   $guest
     * @param int      $extraGuests
     * @param string   $myArgument  with a *description* of this argument, these may also
     *                              span multiple lines
     *
     * @throws MealTicketException
     *
     * @return BaseMealTicket
     */
    public function createNewFromHomeMeal(HomeMeal $meal, MMUser $guest): BaseMealTicket
    {
        // create a new Ticket (copy data)
        $newTicket = $this->createFromHomeMeal($meal, $guest);
        // persist it in order for it to get it's own ID counter.
        $safedMealTicket = $this->persistAndFlush($newTicket);
        // now create a unique ticket number
        $ticketNumber = $this->createTicketNumber($safedMealTicket);
        // update the safed ticket ...
        $safedMealTicket->setNumber($ticketNumber);
        $this->logger->addInfo(
            'Created MealTicket: '.$newTicket->getNumber()
        );
        // safe again, and return the result.
        return $this->persistAndFlush($safedMealTicket);
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param ProMeal   $meal
     * @param MealOffer $mealOffer
     * @param MMUser    $guest
     * @param int       $extraGuests
     * @param string    $myArgument  with a *description* of this argument, these may also
     *                               span multiple lines
     *
     * @throws MealTicketException
     *
     * @return BaseMealTicket
     */
    public function createNewFromProMeal(
        ProMeal $meal,
        MealOffer $mealOffer,
        MMUser $guest
    ): BaseMealTicket {
        // create a new Ticket (copy data)
        $newTicket = $this->createFromProMeal($meal, $mealOffer, $guest);
        // persist it in order for it to get it's own ID counter.
        $safedMealTicket = $this->persistAndFlush($newTicket);
        // now create a unique ticket number
        $ticketNumber = $this->createTicketNumber($safedMealTicket);
        // update the safed ticket ...
        $safedMealTicket->setNumber($ticketNumber);
        $this->logger->addInfo(
            'Created MealTicket: '.$newTicket->getNumber(),
            array('Meal' => $meal, 'MealOffer' => $mealOffer, 'Guest' => $guest)
        );
        // safe again, and return the result.
        return $this->persistAndFlush($safedMealTicket);
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param MealJoinRequest $joinRequest
     *
     * @throws MealTicketException
     *
     * @return BaseMealTicket
     */
    public function restoreFromJoinRequest(MealJoinRequest $joinRequest): BaseMealTicket
    {
        $mealTicket = $this->entityManager->getRepository('ApiBundle:Meal\BaseMealTicket')->findOneBy(
            array(
                'baseMeal' => $joinRequest->getBaseMeal(),
                'guest' => $joinRequest->getCreatedBy(),
            )
        )
        ;

        if (null === $mealTicket) {
            $this->logger->addError('MealTicket für JoinRequest nicht gefunden!');
            throw new MealTicketException('MealTicket für JoinRequest nicht gefunden!');
        }
        $this->logger->addInfo('MealTicket for Guest '.$joinRequest->getCreatedBy().' restored');

        return $mealTicket;
    }

    public function findAllPayedWithPayin(): Collection
    {
        $allPayed = $this->entityManager->getRepository('ApiBundle:Meal\BaseMealTicket')->findBy(
            array(
                'status' => ApiConstants::MEAL_TICKET_STATUS_PAYED,
            )
        );

        $allPayedWithPayin = new ArrayCollection(array());

        foreach ($allPayed as $mealTicket) {
            $transactions = $mealTicket->getTransactions();
            $foundOneValid = false;
            /** @var MealTicketTransaction $trans */
            foreach ($transactions as $trans) {
                // $this->logger->debug(json_encode($trans->__toString()));
                if (EventType::PayinNormalSucceeded === $trans->getMangoEventType()) {
                    $allPayedWithPayin->add($mealTicket);
                    // we found one, so we end the search
                    $foundOneValid = true;
                    break;
                }
            }
            if (!$foundOneValid) {
                $this->logger->info('Skip! NO PAYIN transaction found in '.$mealTicket->getNumber());
            }
        }

        return $allPayedWithPayin;
    }

    /**
     * Helper to get the resourceId from the transactions contained based on mangopay object.
     *
     * for exampel ->getResourceIdByMangopayObj($mealticket, 'PayIn') to get the resourceId connected to PayIn.
     *
     * @param BaseMealTicket $mealTicket
     * @param string         $mangopayObj
     *
     * @return string|null
     */
    public function getResourceIdByMangopayObj(BaseMealTicket $mealTicket, string $mangopayObj): ?string
    {
        $this->logger->debug('MTS->getResourceIdByMangopayObj('.$mangopayObj.')');
        if (0 === $mealTicket->getTransactions()->count()) {
            $this->logger->debug('MTS->getResourceIdByMangopayObj('.$mealTicket->getNumber().'|'.$mangopayObj.')-->0 Transactions');

            return null;
        }
        // filter by successful prior event == SUCCEEDED
        $results = $mealTicket->getTransactions()->filter(
            function (MealTicketTransaction $transaction) use ($mangopayObj, $mealTicket) {
                if ($transaction->getMangoObj() === $mangopayObj &&
                ApiConstants::TRANSACTION_STATUS_SUCCEEDED === $transaction->getMangoEvent()) {
                    return true;
                }
                $this->logger->debug('MTS->getResourceIdByMangopayObj('.$mealTicket->getNumber().'|'.$transaction->getMangoObj().'|'.$transaction->getMangoEvent().')-->');
            }
        );

        /** @var MealTicketTransaction $transaction */
        $transaction = $results->first();
        $this->logger->debug('MTS->getResourceIdByMangopayObj('.$mangopayObj.')-->'.$transaction->getResourceId());

        return $transaction->getResourceId();
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param BaseMeal  $meal
     * @param MMUser    $user
     * @param MealOffer $mealOffer
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     *
     * @return BaseMealTicket
     */
    private function findOneByMealUserOffer(BaseMeal $meal, MMUser $user, MealOffer $mealOffer): BaseMealTicket
    {
        /** @var QueryBuilder $qb */
        $qb = $this->entityManager->getRepository('ApiBundle:Meal\BaseMealTicket')
                                  ->createQueryBuilder('mt')
                                  ->select('mt')
                                  ->where('mt.baseMeal = :baseMeal')
                                  ->andWhere('mt.selectedMealOffer = :mealOffer')
                                  ->andWhere('mt.guest = :user')
                                  ->setParameter('baseMeal', $meal)
                                  ->setParameter('mealOffer', $mealOffer)
                                  ->setParameter('user', $user)
        ;

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param BaseMealTicket $mealTicket
     *
     * @throws MealTicketException
     *
     * @return BaseMealTicket
     */
    private function persistAndFlush(BaseMealTicket $mealTicket): BaseMealTicket
    {
        // error msg prefix
        $_prefix = 'MealTicket update ERROR: ';
        try {
            $this->entityManager->persist($mealTicket);
            $this->entityManager->flush();
        } catch (ORMInvalidArgumentException $ormExc) {
            $this->logger->addError($_prefix.$ormExc->getMessage());
            throw new MealTicketException($ormExc->getMessage());
        } catch (OptimisticLockException $lockException) {
            $this->logger->addError($_prefix.$lockException->getMessage());
            throw new MealTicketException($lockException->getMessage());
        } catch (ORMException $ORMException) {
            $this->logger->addError($_prefix.$ORMException->getMessage());
            throw new MealTicketException($ORMException->getMessage());
        }

        return $mealTicket;
    }

    /**
     * Returns the calculated (price*fee) e.g. 10.00 * 0.10 = 1.00;.
     *
     * @param MealOffer $mealOffer the meal offer containing the price
     *
     * @return float the fee
     */
    private function getProMealPaymentFee(MealOffer $mealOffer): float
    {
        $offerPrice = $mealOffer->getPrice();
        $mmFeePercentage = $this->container->getParameter('mm_fee_promeal');
        $mmFee = $offerPrice * $mmFeePercentage;

        $this->logger->addDebug('getProMealPaymentFee: '.$offerPrice.' * '.$mmFeePercentage.': '.$mmFee);

        return $mmFee;
    }

    /**
     * Returns the calculated (price*fee) e.g. 10.00 * 0.10 = 1.00;.
     *
     * @param BaseMeal $baseMeal the baseMeal containing the sharedPrice
     *
     * @return float the fee
     */
    private function getPaymentFee(BaseMeal $baseMeal): float
    {
        $shared = $baseMeal->getSharedCost();
        $mmFeePercentage = $this->container->getParameter('mm_fee_homemeal');
        $mmFee = $shared * $mmFeePercentage;

        $this->logger->addDebug('getPaymentFee: '.$shared.' * '.$mmFeePercentage.': '.$mmFee);

        return $mmFee;
    }

    /**
     * @todo: Finish PHPDoc!
     * Creates a BaseMealTicket using a HomeMeal.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param HomeMeal $meal
     * @param MMUser   $guest
     * @param int      $extraGuests
     * @param string   $myArgument  with a *description* of this argument, these may also
     *                              span multiple lines
     *
     * @return BaseMealTicket
     */
    private function createFromHomeMeal(HomeMeal $meal, MMUser $guest): BaseMealTicket
    {
        /** @var BaseMealTicket $mealTicket */
        $mealTicket = new BaseMealTicket();
        $mealTicket->setBaseMeal($meal);
        $mealTicket->setPrice($meal->getSharedCost());
        $mealTicket->setCurrency($meal->getSharedCostCurrency());
        $mealTicket->setHost($meal->getHost());
        $mealTicket->setGuest($guest);
        $mealTicket->setMmFee($this->getPaymentFee($meal));
        $mealTicket->setNumberOfTickets(1);
        $mealTicket->setTitel($meal->getTitle());
        $host = $meal->getHost()->getUsername();
        $date = date_format($meal->getStartDateTime(), 'd.m.Y');
        $time = date_format($meal->getStartDateTime(), 'h:i');
        $mealDescription = $meal->getTitle()." von $host am $date um $time.";
        $mealTicket->setDescription('Home-Meal: '.$mealDescription);

        return $mealTicket;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param ProMeal   $meal
     * @param MealOffer $mealOffer
     * @param MMUser    $guest
     * @param int       $extraGuests
     * @param string    $myArgument  with a *description* of this argument, these may also
     *                               span multiple lines
     *
     * @return BaseMealTicket
     */
    private function createFromProMeal(
        ProMeal $meal,
        MealOffer $mealOffer,
        MMUser $guest
    ): BaseMealTicket {
        /** @var BaseMealTicket $mealTicket */
        $mealTicket = new BaseMealTicket();
        $mealTicket->setSelectedMealOffer($mealOffer);
        $mealTicket->setBaseMeal($meal);
        $mealTicket->setPrice($mealOffer->getPrice());
        $mealTicket->setCurrency($meal->getSharedCostCurrency());
        $mealTicket->setHost($meal->getHost());
        $mealTicket->setGuest($guest);
        $mealTicket->setMmFee($this->getProMealPaymentFee($mealOffer));
        $mealTicket->setNumberOfTickets(1);
        $mealTicket->setTitel($meal->getTitle());
        $host = $meal->getHost()->getUsername();
        $date = date_format($meal->getStartDateTime(), 'd.m.Y');
        $time = date_format($meal->getStartDateTime(), 'h:i');
        $mealDescription = $meal->getTableTopic()." im $host am $date um $time. ";
        $mealDescription .= 'Ausgewähltes Angebote: '.$mealOffer->getName().' ';
        $mealDescription .= $mealOffer->getPrice().$mealOffer->getCurrency();
        $mealTicket->setDescription('Restaurant-Meal: '.$mealDescription);

        return $mealTicket;
    }

    /**
     * Creates a unique MealTicketNumber.
     *
     * The unique number follows these patterns:
     * #MM#ID-HOMEMEAL-MEALID-HOSTID-TICKETID
     * #MM#ID-PROEMEAL-MEALID-OFFERID-HOSTID-TICKETID
     *
     * @param BaseMealTicket $mealTicket
     *
     * @return string the MealTicketNumber
     */
    private function createTicketNumber(BaseMealTicket $mealTicket): string
    {
        $meal = $mealTicket->getBaseMeal();
        if (null === $meal) {
            return '#FAILURE!!!#';
        }
        $mealType = $meal->getMealType();

        $mmMealTicketNumber = array(
            $meal->getId(),
            'UNKWOWN!',
            $meal->getHost()->getId(),
            $mealTicket->getId(),
        );
        switch ($mealType) {
            case ApiConstants::MEAL_TYPE_HOME:
                $mmMealTicketNumber = array(
                    $meal->getId(),
                    ApiConstants::MEAL_TYPE_HOME,
                    $meal->getHost()->getId(),
                    $mealTicket->getId(),
                );
                break;
            case ApiConstants::MEAL_TYPE_PRO:
                $mmMealTicketNumber = array(
                    $meal->getId(),
                    $mealTicket->getSelectedMealOffer()->getId(),
                    ApiConstants::MEAL_TYPE_PRO,
                    $meal->getHost()->getId(),
                    $mealTicket->getId(),
                );
                break;
            default:
                // hope that never is a case ...
                break;
        }

        return strtoupper('#MM#'.implode('-', $mmMealTicketNumber));
    }
}
