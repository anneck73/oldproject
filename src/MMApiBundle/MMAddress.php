<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMApiBundle;

use Bazinga\Bundle\GeocoderBundle\Geocoder\LoggableGeocoder;
use Doctrine\ORM\EntityManager;
use Geocoder\Model\Address as GeoAddress;
use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;
use MMApiBundle\Entity\Address;
use MMApiBundle\Entity\Meal;
use MMApiBundle\Exceptions\MMGeoCodeException;
use Monolog\Logger;

/**
 * The MMAddress is a Symfony-Service provided by this bundle.
 *
 * The MMAddress Service provides access to Address data used
 * by MMUser and Meal entities.
 */
final class MMAddress
{
    const SERVICE_NAME = 'mm.address';
    /**
     * This service class uses the EntityManager.
     *
     * @var EntityManager
     */
    private $em;

    /**
     * This services class uses a geocoder (GMaps).
     *
     * @var LoggableGeocoder
     */
    private $geoCoder;

    /**
     * This service class writes log entries.
     *
     * @var Logger;
     */
    private $log;

    public function __construct(EntityManager $pEM, LoggableGeocoder $pGeocoder, Logger $pLogger)
    {
        $this->em = $pEM;
        $this->geoCoder = $pGeocoder;
        $this->log = $pLogger;
    }

    public function __toString()
    {
        return __CLASS__;
    }

    public function updateAddressLocationFrom(Meal $pMeal)
    {
        $newLoc = $pMeal->getLocationAddress();
        $pMeal->getAddress()->setLocationAddress($newLoc);
        $this->em->persist($pMeal);
        $this->em->flush();
    }

    /**
     * The update method.
     *
     * @param Meal $pMeal the meal to update the associated address from a new location address
     *
     * @return Address the up-to-date address entity
     */
    public function updateAddressByLocAddress(Meal $pMeal): Address
    {
        $retAddress = $pMeal->getAddress();
        if ($this->changedLocAddress($pMeal)) {
            // remove old address from meal ...
            $oldAddress = $pMeal->getAddress();
            $oldAddress->setMeal(null);
            // this will leave the address data in the database
            // but detached from it's meal.
            $this->em->persist($oldAddress);
            $this->em->flush();
            /** @var Address */
            $retAddress = $this->create($pMeal);
            $pMeal->setLatitude($retAddress->getLocation()->getLatitude());
            $pMeal->setLongitude($retAddress->getLocation()->getLongitude());
        }

        return $retAddress;
    }

    /**
     * Checks if the location address of the Meal changed compared to its
     * containing Address entity.
     *
     *
     * @param Meal $pMeal the meal to check for changes
     *
     * @return bool true if changed
     */
    public function changedLocAddress(Meal $pMeal)
    {
        $changed = false;
        if ($pMeal->getLocationAddress() !== $pMeal->getAddress()->getLocationAddress()) {
            $changed = true;
        }

        return $changed;
    }

    /**
     * Creates a new Adress Entity using the Meal specified.
     *
     * The location Adress parameter from pMeal is used to find an existing Adress and copy its
     * values if found. This safes the GMaps call to geocode.
     * If there is nothing found a new Address entity is created and associated to the $pMeal.
     *
     * @param Meal $pMeal the Meal entity to use to create the address
     *
     * @throws MMGeoCodeException if creation fails due to GMaps failures
     *
     * @return Address the new Address entity
     */
    public function create(Meal $pMeal)
    {
        $locationAddress = $pMeal->getLocationAddress();
        $this->log->addDebug(sprintf('MMAddress:create with pLocAd: %s', $locationAddress));

        /** @var Address $retAddress */
        $retAddress = $this->geocode($pMeal);

        if (!$this->isValid($retAddress)) {
            throw new MMGeoCodeException(
                $locationAddress,
                sprintf('Geocoding for %s failed! Not Valid!', $locationAddress)
            );
        }

        // @todo: only do this if changed...
        if ($pMeal->hasAddress()) {
            $oldAddress = $pMeal->getAddress();
            $this->em->remove($oldAddress);
            $this->em->flush();
        }

        // Connect the two ...
        $retAddress->setMeal($pMeal);
        $pMeal->setAddress($retAddress);
        $pMeal->setLatitude($retAddress->getLocation()->getLatitude());
        $pMeal->setLongitude($retAddress->getLocation()->getLongitude());

        $this->em->persist($retAddress);
        $this->em->persist($pMeal);
        $this->em->flush();

        return $retAddress;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param array $pMeals
     */
    public function repair(array $pMeals)
    {
        /** @var Meal $meal */
        foreach ($pMeals as $meal) {
            if (null === $meal->getAddress()) {
                try {
                    $newAddress = $this->create($meal);
                    $meal->setAddress($newAddress);
                    $this->em->persist($meal);
                } catch (MMGeoCodeException $geoCodeException) {
                    $this->log->addAlert(
                        sprintf(
                            'Meal ID(%s) removed during repair, geocode of \'%s\' failed',
                            $meal->getId(),
                            $meal->getLocationAddress()
                        )
                    );
                    $this->em->remove($meal);
                }
            } else {
                $testAddress = $meal->getAddress();
                if (!$this->isValid($testAddress)) {
                    $meal->setStatus(Meal::$STATUS_STOPPED);
                }
            }
        }
        $this->em->flush();
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $pLocationAddress
     *
     * @return Address|null
     */
    public function getCopyOf(string $pLocationAddress)
    {
        if ($this->exists($pLocationAddress)) {
            $add = $this->getAddress($pLocationAddress);

            return $this->copy($add, new Address());
        }

        return null;
    }

    /**
     * Checks if the specified location address already exists as a MMAddress entity.
     *
     * @param string $pLocationAddress the location address to search for
     *
     * @return bool true if found
     */
    public function exists(string $pLocationAddress)
    {
        $exist = false;
        $foundArray = $this->em->getRepository('MMApiBundle:Address')->findBy(
            array(
                'locationAddress' => $pLocationAddress,
            )
        )
        ;
        if (\count($foundArray) > 0) {
            $this->log->addDebug(sprintf('MMAddress HIT locAddress: %s', $pLocationAddress));
            $exist = true;
        } else {
            $this->log->addDebug(sprintf('MMAddress MISS locAddress: %s', $pLocationAddress));
        }

        return $exist;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param Meal $pMeal
     *
     * @throws MMGeoCodeException
     *
     * @return Address
     */
    private function geocode(Meal $pMeal)
    {
        $locationAddress = $pMeal->getLocationAddress();
        try {
            $allResults = $this->geoCoder->using('google_maps')->geocode($locationAddress)->all();
        } catch (\Exception $exception) {
            throw new MMGeoCodeException(
                $locationAddress,
                sprintf('Geocoding for %s failed!', $locationAddress),
                $exception->getCode(),
                $exception
            );
        }
        $newAddress = new Address();
        $locations = array();
        if (\count($allResults) > 1) {
            foreach ($allResults as $geoAddress) {
                array_push($locations, $geoAddress->getLocality());
            }
            $flatLocations = implode(', ', $locations);
            throw new MMGeoCodeException(
                $locationAddress,
                sprintf('Geocoding for %s failed, multiple locations found!', $flatLocations)
            );
        }

        /** @var GeoAddress $geoAddress */
        foreach ($allResults as $geoAddress) {
            if (null === $geoAddress->getPostalCode()) {
                throw new MMGeoCodeException(
                    $locationAddress,
                    sprintf('Geocoding for %s failed no PostalCode!', $locationAddress)
                );
            }
            if (null === $geoAddress->getStreetNumber()) {
                throw new MMGeoCodeException(
                    $locationAddress,
                    sprintf('Geocoding for %s failed no StreetNumber!', $locationAddress)
                );
            }
            if (null === $geoAddress->getLocality()) {
                throw new MMGeoCodeException(
                    $locationAddress,
                    sprintf('Geocoding for %s failed no City!', $locationAddress)
                );
            }
            $point = new Point($geoAddress->getLatitude(), $geoAddress->getLongitude());
            $newAddress->setLocation($point);
            $newAddress->setCountryCode($geoAddress->getCountryCode());
            $newAddress->setCity($geoAddress->getLocality());
            $newAddress->setPostalCode($geoAddress->getPostalCode());
            $newAddress->setCountry($geoAddress->getCountry());
            $newAddress->setStreetName($geoAddress->getStreetName());
            $newAddress->setStreetNumber($geoAddress->getStreetNumber());

            if ($geoAddress->getAdminLevels()->count() > 0) {
                foreach ($geoAddress->getAdminLevels()->all() as $adminLevel) {
                    if (1 === $adminLevel->getLevel()) {
                        $newAddress->setState($adminLevel->getName());
                    }
                }
            }
        }

        return $newAddress;
    }

    private function isValid(Address $testAddress)
    {
        $retValue = false;

        if (null !== $testAddress->getCity()
            && null !== $testAddress->getPostalCode()
            && null !== $testAddress->getStreetNumber()
        ) {
            $retValue = true;
        }

        return $retValue;
    }

    /**
     * Returns the Address entity specified by the location address string.
     *
     * @param string $pLocationAddress the location address string to identifiy the address
     *
     * @return Address|object|null the address specified by the location address string
     */
    private function getAddress(string $pLocationAddress): Address
    {
        return $this->em->getRepository('MMApiBundle:Address')->findOneBy(
            array(
                'locationAddress' => $pLocationAddress,
            )
        )
            ;
    }

    private function copy(Address $pSource, Address $pTarget)
    {
        $pTarget->setLocation($pSource->getLocation());
        $pTarget->setCountryCode($pSource->getCountryCode());
        $pTarget->setCity($pSource->getCity());
        $pTarget->setPostalCode($pSource->getPostalCode());
        $pTarget->setCountry($pSource->getCountry());
        $pTarget->setStreetName($pSource->getStreetName());
        $pTarget->setStreetNumber($pSource->getStreetNumber());

        return $pTarget;
    }
}
