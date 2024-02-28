<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use Mealmatch\ApiBundle\Entity\LogEntry;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
use Monolog\Handler\AbstractProcessingHandler;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The LogEntryHandler Service writes log messages into the DB,
 * if possible! e.g. If there is an ERROR causing the EntityManager to close, we can not report this into the DB.
 */
class PayPalLogEntryHandlerService extends AbstractProcessingHandler
{
    private $initialized;
    private $entityManager;
    private $channel = 'paypal';
    /**
     * @todo: Finish PHPDoc!
     *
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @todo: Finish PHPDoc!
     *
     * @var bool
     */
    private $debug;

    /**
     * LogEntryHandlerService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RequestStack           $requestStack
     * @param bool                   $debug
     */
    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack, bool $debug)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->debug = $debug;
    }

    /**
     * Writes the record down to the log of the implementing handler.
     *
     * @param array $record
     */
    protected function write(array $record)
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        if ($this->channel !== $record['channel']) {
            return;
        }

        // The new LogEntry ...
        $log = new LogEntry();
        $log->setLevel($record['level_name']);
        $message = $record['message'];

        // Fill with request ...
        if (null !== $this->requestStack->getCurrentRequest()) {
            // nice, trace it ...
            $query = $this->requestStack->getCurrentRequest()->query->all();
            $req = $this->requestStack->getCurrentRequest()->request->all();
            $headers = $this->requestStack->getCurrentRequest()->headers->all();
            $log->setRequestInfos(json_encode(array($query, $req, $headers)));
        }

        // Only if there is a context specified ...
        if (!empty($record['context'])) {
            foreach ($record['context'] as $ctxKey => $ctxValue) {
                if ($ctxValue instanceof BaseMealTicket) {
                    $message .= $ctxKey.':\n\t\t '.$ctxValue->getJson();
                }
            }
        }

        $log->setMessage('PayPal->'.$message);
        // Check if EntityManager is still open ...
        if ($this->entityManager->isOpen()) {
            $this->entityManager->persist($log);
            $this->entityManager->flush();
        }
    }

    private function initialize()
    {
        $this->initialized = true;
    }
}
