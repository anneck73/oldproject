<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\GameLogicBundle\EventSubscriber;

use Doctrine\ORM\EntityManager;
use Mealmatch\GameLogicBundle\Entity\Score;
use Mealmatch\GameLogicBundle\Event\Scored;
use Mealmatch\GameLogicBundle\Exceptions\GamePersistenceException;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher as EventDispatcher;

/**
 * Subscribed to Scored Events and persists the score.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class ScoredSubscriber implements EventSubscriberInterface
{
    /**
     * @var Logger the logger to use ...
     */
    private $logger;
    /**
     * The EventDispatcher is used to trigger new events ...
     *
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var EntityManager the entity manager
     */
    private $em;

    public function __construct(Logger $pLogger, $pDispatcher, EntityManager $pEm)
    {
        $this->em = $pEm;
        $this->logger = $pLogger;
        $this->dispatcher = $pDispatcher;
        $this->logger->addDebug(
            sprintf('Created %s', __CLASS__)
        );
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
     * @return mixed
     */
    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return array(
            Scored::USER => 'onUserScored',
        );
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
    public function onUserScored(Scored $pScored)
    {
        $this->logger->addDebug(
            sprintf(
                'OnUserScored %s %s %s %s',
                $pScored->getScore()->getUser(),
                $pScored->getScore()->getName(),
                $pScored->getScore()->getValue(),
                $pScored->getScore()->getType()
            )
        );

        // Try and find a score entry ...
        /** @var Score $userScore */
        $userScore = $this->em->getRepository('MMGameLogicBundle:Score')->findOneBy(
            array(
                'name' => $pScored->getScore()->getName(),
                'type' => $pScored->getScore()->getType(),
                'createdBy' => $pScored->getScore()->getUser(),
            )
        )
        ;

        if (null === $userScore) {
            // not scored yet ... let's do it ...
            $userScore = new Score();
            $userScore->setName($pScored->getScore()->getName());
            $userScore->setType($pScored->getScore()->getType());
            $userScore->setValue($pScored->getScore()->getValue());
        } else {
            // already scored, this mean we ADD to the value ...
            $currentValue = $userScore->getValue();
            $newValue = $currentValue + $pScored->getScore()->getValue();
            $userScore->setValue($newValue);
        }

        // Try and store the score ...
        try {
            $this->em->persist($userScore);
            $this->em->flush();
        } catch (\Exception $exception) {
            $this->logger->addError('Failed to persist score! '.$exception->getMessage());
            throw new GamePersistenceException('Failed to persist score: ', $exception->getMessage());
        }
        $this->logger->addInfo(
            sprintf(
                'UserScored %s %s %s %s',
                $pScored->getScore()->getUser(),
                $userScore->getName(),
                $userScore->getValue(),
                $userScore->getType()
            )
        );
    }
}
