<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Entity\Meal;

use Doctrine\ORM\Mapping as ORM;
use Mealmatch\ApiBundle\Entity\AbstractEntity;

/**
 * The MealEvent contains start, end, allDay and recurring time conditions for the Meal.
 *
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class MealEvent does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 * @ORM\Entity()
 */
class MealEvent extends AbstractEntity implements MealData
{
    /**
     * @todo: Finish PHPDoc!
     *
     * @var
     * @ORM\Column(name="start_date_time", type="datetime")
     */
    protected $startDateTime;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var
     * @ORM\Column(name="end_date_time", type="datetime", nullable=true)
     */
    protected $endDateTime;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var string
     * @ORM\Column(name="timezone", type="string", length=25)
     */
    protected $timezone = 'Europe/Berlin';
    /**
     * @todo: Finish PHPDoc!
     *
     * @var bool
     * @ORM\Column(name="all_day", type="boolean")
     */
    protected $allDay = false;
    /**
     * @todo: Finish PHPDoc!
     *
     * @var bool
     * @ORM\Column(name="reoccuring", type="boolean")
     */
    protected $reoccuring = false;
    /**
     * @todo: Finish PHPDoc!
     *
     * @var string
     * @ORM\Column(name="rrule", type="string")
     */
    protected $rrule = 'FREQ=WEEKLY;COUNT=20';

    public function __toString()
    {
        return 'MealEvent#'.$this->getId();
    }

    /**
     * @return mixed
     */
    public function getStartDateTime()
    {
        return $this->startDateTime;
    }

    /**
     * @param mixed $startDateTime
     *
     * @return MealEvent
     */
    public function setStartDateTime($startDateTime)
    {
        $this->startDateTime = $startDateTime;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEndDateTime()
    {
        return $this->endDateTime;
    }

    /**
     * @param mixed $endDateTime
     *
     * @return MealEvent
     */
    public function setEndDateTime($endDateTime)
    {
        $this->endDateTime = $endDateTime;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAllDay(): bool
    {
        return $this->allDay;
    }

    /**
     * @param bool $allDay
     *
     * @return MealEvent
     */
    public function setAllDay(bool $allDay): self
    {
        $this->allDay = $allDay;

        return $this;
    }

    /**
     * @return bool
     */
    public function isReoccuring(): bool
    {
        return $this->reoccuring;
    }

    /**
     * @param bool $reoccuring
     *
     * @return MealEvent
     */
    public function setReoccuring(bool $reoccuring): self
    {
        $this->reoccuring = $reoccuring;

        return $this;
    }

    /**
     * @return string
     */
    public function getRrule(): string
    {
        return $this->rrule;
    }

    /**
     * @param string $rrule
     *
     * @return MealEvent
     */
    public function setRrule(string $rrule): self
    {
        $this->rrule = $rrule;

        return $this;
    }

    /**
     * @return string
     */
    public function getTimezone(): string
    {
        return $this->timezone;
    }

    /**
     * @param string $timezone
     *
     * @return MealEvent
     */
    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }
}
