<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMApiBundle\EventSubscriber;

use Doctrine\Common\Collections\Collection;
use FOS\MessageBundle\Event\FOSMessageEvents;
use FOS\MessageBundle\Event\MessageEvent;
use FOS\MessageBundle\Event\ThreadEvent;
use FOS\MessageBundle\Model\ThreadMetadata;
use MMUserBundle\Entity\MMUser;
use Swift_Mailer;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

/**
 * @todo: Finish PHPDoc!
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
final class MessagesSubscriber implements EventSubscriberInterface
{
    const KEY_SUBJECT = 'subject';
    /** @var FlashBag */
    private $flashBag;
    /** @var Swift_Mailer */
    private $mailer;
    /** @var TwigEngine */
    private $twig;

    /**
     * RegistrationListener constructor.
     */
    public function __construct(FlashBag $pFlashBag, Swift_Mailer $pSwift, TwigEngine $pTwig)
    {
        $this->flashBag = $pFlashBag;
        $this->mailer = $pSwift;
        $this->twig = $pTwig;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FOSMessageEvents::POST_DELETE => 'onPostDelete',
            FOSMessageEvents::POST_SEND => 'onPostSend',
            FOSMessageEvents::POST_UNDELETE => 'onPostUnDelete',
            FOSMessageEvents::POST_UNREAD => 'onPostUnRead',
        );
    }

    public function onPostDelete(ThreadEvent $pEvent)
    {
        $subject = $pEvent->getThread()->getSubject();
        $this->flashBag->add('danger', 'Die Nachrichten zum Thema: '.$subject.' wurden gelöscht!');
    }

    public function onPostSend(MessageEvent $pEvent)
    {
        /** @var MMUser[] $receivers */
        $receivers = $this->extractReceivers($pEvent);

        /** @var array $messageData */
        $messageData = $this->extractMessageData($pEvent);

        /*
        // Send mails ...
        $this->sendMailToAllReceivers($receivers, $messageData);
        $this->flashBag->add(
            'info',
            'Deine Nachricht zum Thema: '.$messageData[static::KEY_SUBJECT].' wurde gesendet!'
        );
        */
    }

    public function onPostUnDelete()
    {
        // $this->flashBag->add('info', 'onPostUndelete()');
    }

    public function onPostUnRead()
    {
        // $this->flashBag->add('info', 'onPostUnRead()');
    }

    /**
     * Extracts the receivers from the ThreadMetadata of the MessageEvent.
     *
     * It filters the getMessage()->getSender() from all participiants in the ThreadMetadata.
     *
     * @param MessageEvent $pEvent the event to extract the receivers from
     *
     * @return MMUser[] the array of receivers
     */
    private function extractReceivers(MessageEvent $pEvent)
    {
        /** @var MMUser $sender */
        $sender = $pEvent->getMessage()->getSender();
        /** @var Collection $metaData */
        $metaData = $pEvent->getThread()->getAllMetaData();
        /** @var MMUser[] $receivers */
        $receivers = array();
        foreach ($metaData as $data) {
            if ($data instanceof ThreadMetadata) {
                $participiant = $data->getParticipant();
                // we filter the sender by ID ...
                if ($participiant->getId() !== $sender->getId()) {
                    array_push($receivers, $participiant);
                }
            }
        }

        return $receivers;
    }

    /**
     * Extracts message "data" from the MessageEvent and returns it in an array.
     *
     * The array which is returned contains the fields subject, body and sender.
     *
     * It looks like this, and is filled with values accordingly.
     *
     *  $messageData = [
     *      'subject' =>
     *      'body' =>
     *      'sender' =>
     *  ];
     *
     *
     * @param MessageEvent $pEvent the MessageEvent to extract the data from
     *
     * @return array with subject, body and sender
     */
    private function extractMessageData(MessageEvent $pEvent)
    {
        $messageData = array(
            static::KEY_SUBJECT => $pEvent->getMessage()->getThread()->getSubject(),
            'body' => $pEvent->getMessage()->getBody(),
            'sender' => $pEvent->getMessage()->getSender(),
            'threadID' => $pEvent->getMessage()->getThread()->getId(),
        );

        return $messageData;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param        $receivers
     * @param        $messageData
     * @param string $myArgument  with a *description* of this argument, these may also
     *                            span multiple lines
     */
    private function sendMailToAllReceivers($receivers, $messageData)
    {
        foreach ($receivers as $receiver) {
            /** @var \Swift_Mime_Message $message */
            $message = \Swift_Message::newInstance()
                                     ->setSubject('[Persönliche Nachricht]: '.$messageData[static::KEY_SUBJECT])
                                     ->setFrom('mailer@mealmatch.de')
                                     ->setTo($receiver->getEmail())
                                     ->setBody(
                                         $this->twig->render(
                                             '@API/Emails/Message-Send.html.twig',
                                             array(
                                                 'MessageData' => $messageData,
                                                 'Receiver' => $receiver,
                                             )
                                         ),
                                         'text/html'
                                     )
            ;

            $sentMessages = $this->mailer->send($message);
            if ($sentMessages > 0) {
                $this->flashBag->add('info', 'Eine Emailbenachrichtigung wurde auch versendet.');
            } else {
                $this->flashBag->add('danger', 'Die Emailbenachrichtigung konnte nicht versendet werden!');
            }
        }
    }
}
