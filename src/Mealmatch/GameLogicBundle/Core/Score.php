<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\GameLogicBundle\Core;

class Score implements ScoreInterface
{
    const POINT_TYPE = 'point';
    const CURRENCY_TYPE = 'currency';
    const COUNTER_TYPE = 'counter';

    /**
     * The value of the score.
     *
     * @var int
     */
    private $value;

    /**
     * The type of score.
     *
     * @var string the type of score
     */
    private $type;

    /**
     * The score name.
     *
     * @var string the name of the score
     */
    private $name;

    /**
     * Score constructor.
     *
     * @param int    $pValue
     * @param string $pName
     * @param string $pType
     */
    public function __construct(int $pValue, string $pName = '-', $pType = self::POINT_TYPE)
    {
        $this->name = $pName;
        $this->value = $pValue;
        $this->type = $pType;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
