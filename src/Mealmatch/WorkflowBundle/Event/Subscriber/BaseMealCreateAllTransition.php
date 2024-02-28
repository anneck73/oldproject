<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\WorkflowBundle\Event\Subscriber;

use Doctrine\Common\Collections\ArrayCollection;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Entity\Meal\BaseMeal;
use Mealmatch\ApiBundle\Entity\Meal\HomeMeal;
use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Mealmatch\ApiBundle\Model\MealServiceData;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

/**
 * This EventSubscriber executes on 'workflow.base_meal.transition.create_meals' and uses the mealService
 * to createAllMeals as specified by the root-Meal. And this will actually conclude the transition.
 *
 * It uses the FOS:message services to generate a Message from the SYSTEM user to the user executing the transition.
 */
class BaseMealCreateAllTransition extends AbstractBaseMealSubscriber implements EventSubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            'workflow.base_meal.transition.create_meals' => array('createMeals'),
        );
    }

    /**
     * Uses the mealService to create all meals as specified.
     *
     * @param Event $event the transition event
     */
    public function createMeals(Event $event)
    {
        // Execute the task of creating all Meals using the per-type Meal-Service.
        /** @var BaseMeal $meal */
        $meal = $event->getSubject();
        $result = new MealServiceData('HomeMeal', $meal);
        if ($meal instanceof HomeMeal) {
            $result = $this->mealService->createAllHomeMealEvents($meal, ApiConstants::MEAL_STATUS_READY);
        }
        if ($meal instanceof ProMeal) {
            $result = $this->mealService->createAllProMealEvents($meal, ApiConstants::MEAL_STATUS_READY);
        }

        $this->doLog($event);

        // Create the SystemMessage content.
        /** @var BaseMeal $meal */
        $meal = $event->getSubject();
        $mealTitle = $meal->getTitle();
        $createdMeals = $result->getData('createdMeals');
        $createdCount = (new ArrayCollection($result->getData('createdMeals')))->count();
        $body = $this->createMessageBodyHTML($createdMeals);

        // Send the SystemMessage.
        $this->sendFosMessageForCreateAllMeals($createdCount, $mealTitle, $body);
    }

    /**
     * Simple helpe to log event details.
     *
     * @param Event $event the transition event
     */
    private function doLog(Event $event)
    {
        $this->logger->alert(sprintf(
            'Meal (id: "%s") performed transaction "%s" from "%s" to "%s"',
            $event->getSubject()->getId().':'.$event->getSubject()->getStatus(),
            $event->getTransition()->getName(),
            implode(', ', $event->getTransition()->getFroms()),
            implode(', ', $event->getTransition()->getTos())
        ));
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param        $createdMeals
     * @param string $myArgument   with a *description* of this argument, these may also
     *                             span multiple lines
     *
     * @return string
     */
    private function createMessageBodyHTML($createdMeals): string
    {
        $body = '<p>Ergebnis: </p><ul>';
        /** @var BaseMeal $meal */
        foreach ($createdMeals as $meal) {
            $start = '???';
            $end = '???';
            try {
                $start = $meal->getMealEvent()->getStartDateTime()->format('d.m.y H:i');
                if (null !== $meal->getMealEvent()->getEndDateTime()) {
                    $end = $meal->getMealEvent()->getEndDateTime()->format('H:i');
                } else {
                    $end = '-/-';
                }
            } catch (\Exception $exception) {
                $this->logger->critical('Failed to process MealEvent(): '.$exception->getMessage());
            }

            $body .= '<li>HomeMeal: '.$meal->getTitle()
                ."von $start bis $end im Status: ".$meal->getStatus().' erzeugt</li>';
        }
        $body .= '</ul>';

        return $body;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param        $createdCount
     * @param        $mealTitle
     * @param        $body
     * @param string $myArgument   with a *description* of this argument, these may also
     *                             span multiple lines
     */
    private function sendFosMessageForCreateAllMeals($createdCount, $mealTitle, $body): void
    {
        try {
            $user = $this->storage->getToken()->getUser();
            $sender = $this->entityManager->getRepository('MMUserBundle:MMUser')->findOneBy(
                array('username' => 'SYSTEM')
            );
            $message = $this->composer->newThread()
                ->addRecipient($user)
                ->setSubject("Es wurden $createdCount HomeMeals mit dem Title: $mealTitle erstellt!")
                ->setSender($sender)
                ->setBody($body)
                ->getMessage();

            $this->sender->send($message);
            $this->logger->info('Send FOS:Message.');
        } catch (\Exception $exception) {
            $this->logger->alert('Failed to send FOS:Message!');
        }
    }
}
