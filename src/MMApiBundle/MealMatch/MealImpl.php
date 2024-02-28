<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMApiBundle\MealMatch;

/**
 * The "Meal" Business Model ...
 *
 * @todo: Finish PHPDoc
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
final class MealImpl implements Meal
{
    public static $MEAL_CATEGORIES;

    public function __construct()
    {
        /* @var  MEAL_CATEGORIES */
        self::$MEAL_CATEGORIES = array(
            'Alle',
            'deutsch',
            'italienisch',
            'chinesisch',
            'vegan',
            'vegetarisch',
            'koscher',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getLocationAddress()
    {
        // TODO: Implement getLocationAddress() method.
    }

    /**
     * {@inheritdoc}
     */
    public function setLocationAddress($locationAddress)
    {
        // TODO: Implement setLocationAddress() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getSharedCostCurrency()
    {
        // TODO: Implement getSharedCostCurrency() method.
    }

    /**
     * {@inheritdoc}
     */
    public function setSharedCostCurrency($sharedCostCurrency)
    {
        // TODO: Implement setSharedCostCurrency() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getSharedCost()
    {
        // TODO: Implement getSharedCost() method.
    }

    /**
     * {@inheritdoc}
     */
    public function setSharedCost($sharedCost)
    {
        // TODO: Implement setSharedCost() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        // TODO: Implement getId() method.
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        // TODO: Implement setTitle() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        // TODO: Implement getTitle() method.
    }

    /**
     * {@inheritdoc}
     */
    public function setCategory($category)
    {
        // TODO: Implement setCategory() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getCategory()
    {
        // TODO: Implement getCategory() method.
    }

    /**
     * {@inheritdoc}
     */
    public function setStarter($starter)
    {
        // TODO: Implement setStarter() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getStarter()
    {
        // TODO: Implement getStarter() method.
    }

    /**
     * {@inheritdoc}
     */
    public function setMain($main)
    {
        // TODO: Implement setMain() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getMain()
    {
        // TODO: Implement getMain() method.
    }

    /**
     * {@inheritdoc}
     */
    public function setDesert($desert)
    {
        // TODO: Implement setDesert() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getDesert()
    {
        // TODO: Implement getDesert() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        // TODO: Implement getDescription() method.
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        // TODO: Implement setDescription() method.
    }
}
