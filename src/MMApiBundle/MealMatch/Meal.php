<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMApiBundle\MealMatch;

interface Meal
{
    /**
     * @return string
     */
    public function getLocationAddress();

    /**
     * @param string $locationAddress
     *
     * @return \MMApiBundle\Entity\Meal
     */
    public function setLocationAddress($locationAddress);

    /**
     * @return string
     */
    public function getSharedCostCurrency();

    /**
     * @param string $sharedCostCurrency
     *
     * @return \MMApiBundle\Entity\Meal
     */
    public function setSharedCostCurrency($sharedCostCurrency);

    /**
     * @return mixed
     */
    public function getSharedCost();

    /**
     * @param float
     * @param mixed $sharedCost
     *
     * @return \MMApiBundle\Entity\Meal
     */
    public function setSharedCost($sharedCost);

    /**
     * Get id.
     *
     * @return int
     */
    public function getId();

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return \MMApiBundle\Entity\Meal
     */
    public function setTitle($title);

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set category.
     *
     * @param \stdClass $category
     *
     * @return \MMApiBundle\Entity\Meal
     */
    public function setCategory($category);

    /**
     * Get category.
     *
     * @return string $category
     */
    public function getCategory();

    /**
     * Set starter.
     *
     * @param string $starter
     *
     * @return \MMApiBundle\Entity\Meal
     */
    public function setStarter($starter);

    /**
     * Get starter.
     *
     * @return string
     */
    public function getStarter();

    /**
     * Set main.
     *
     * @param string $main
     *
     * @return \MMApiBundle\Entity\Meal
     */
    public function setMain($main);

    /**
     * Get main.
     *
     * @return string
     */
    public function getMain();

    /**
     * Set desert.
     *
     * @param string $desert
     *
     * @return \MMApiBundle\Entity\Meal
     */
    public function setDesert($desert);

    /**
     * Get desert.
     *
     * @return string
     */
    public function getDesert();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     *
     * @return \MMApiBundle\Entity\Meal
     */
    public function setDescription($description);
}
