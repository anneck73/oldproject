<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\MealMatch\Swift;

use Swift_ConfigurableSpool;
use Swift_Mime_Message;
use Swift_Transport;

/**
 * @todo: This could be implemented to put the spool files into S3
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class S3Spool does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class S3Spool extends Swift_ConfigurableSpool
{
    /**
     * Starts this Spool mechanism.
     */
    public function start()
    {
        // TODO: Implement start() method.
    }

    /**
     * Stops this Spool mechanism.
     */
    public function stop()
    {
        // TODO: Implement stop() method.
    }

    /**
     * Tests if this Spool mechanism has started.
     *
     * @return bool
     */
    public function isStarted()
    {
        // TODO: Implement isStarted() method.
    }

    /**
     * Queues a message.
     *
     * @param Swift_Mime_Message $message The message to store
     *
     * @return bool Whether the operation has succeeded
     */
    public function queueMessage(Swift_Mime_Message $message)
    {
        // TODO: Implement queueMessage() method.
    }

    /**
     * Sends messages using the given transport instance.
     *
     * @param Swift_Transport $transport        A transport instance
     * @param string[]        $failedRecipients An array of failures by-reference
     *
     * @return int The number of sent emails
     */
    public function flushQueue(Swift_Transport $transport, &$failedRecipients = null)
    {
        // TODO: Implement flushQueue() method.
    }
}
