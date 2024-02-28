<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Mealmatch\ApiBundle\Entity\EntityData;
use Mealmatch\ApiBundle\Entity\Meal\MealAddress;
use Mealmatch\ApiBundle\Entity\Restaurant\RestaurantAddress;
use Mealmatch\ApiBundle\Exceptions\InvalidArgumentException;
use Mealmatch\ApiBundle\Exceptions\ServiceDataException;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class GeoAddressServiceData does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class GeoAddressServiceData extends AbstractServiceDataManager
{
    /**
     * GeoAddressServiceData constructor.
     *
     * @param EntityData $entity
     */
    public function __construct(EntityData $entity)
    {
        parent::__construct('GeoAddress', $entity);
        $this->setData(ServiceDataSpecification::MANAGED_ENTITY_KEY, false);
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param ArrayCollection $geoLocations
     *
     * @throws InvalidArgumentException
     */
    public function addGeoLocation(ArrayCollection $geoLocations)
    {
        if ($geoLocations->count() > 1) {
            throw new InvalidArgumentException('Only One GeoLocation can be added! Count: '.$geoLocations->count());
        }
        $this->setData('GeoLocation', $geoLocations);
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @throws ServiceDataException
     *
     * @return MealAddress
     */
    public function getMealAddress(): MealAddress
    {
        $entity = $this->getEntity('GeoAddress');
        if ($entity instanceof MealAddress) {
            return $entity;
        }
        if (null === $entity) {
            throw new ServiceDataException('Could not find entity using entityKey: GeoAdress!!!! (null)');
        }
        throw new ServiceDataException('GeoAdress entity is not an instanceof MealAddress!!!!');
    }

    /**
     * @throws ServiceDataException
     *
     * @return RestaurantAddress
     */
    public function getRestaurantAddress(): RestaurantAddress
    {
        $entity = $this->getEntity('GeoAddress');
        if ($entity instanceof RestaurantAddress) {
            return $entity;
        }
        if (null === $entity) {
            throw new ServiceDataException('Could not find entity using entityKey: GeoAdress!!!! (null)');
        }
        throw new ServiceDataException('GeoAdress entity is not an instanceof RestaurantAddress!!!!');
    }
}
