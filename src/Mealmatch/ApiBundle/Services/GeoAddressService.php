<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Services;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Geocoder\Collection;
use Geocoder\Exception\Exception;
use Geocoder\Location;
use Geocoder\Model\Address as GeoAddress;
use Geocoder\Provider\GoogleMaps\Model\GoogleAddress;
use Geocoder\ProviderAggregator;
use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;
use Mealmatch\ApiBundle\ApiConstants;
use Mealmatch\ApiBundle\Entity\GeoCodeable;
use Mealmatch\ApiBundle\Entity\Meal\MealAddress;
use Mealmatch\ApiBundle\Entity\Restaurant\RestaurantAddress;
use Mealmatch\ApiBundle\Entity\SameAddress;
use Mealmatch\ApiBundle\Exceptions\GeoCodeException;
use Mealmatch\ApiBundle\Exceptions\InvalidArgumentException;
use Mealmatch\ApiBundle\Exceptions\ServiceDataException;
use Mealmatch\ApiBundle\Model\GeoAddressServiceData;
use Monolog\Logger;
use ReflectionObject;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Intl\Exception\NotImplementedException;

class GeoAddressService
{
    public const MEAL_ADDRESS_OPTION = 'MealAddress';
    public const RESTAURANT_ADDRESS_OPTION = 'RestaurantAddress';
    private $logger;
    private $entityManager;
    private $translator;
    private $geoCoder;
    private $providerAggregator;

    private static $defaultOptions = array(
        'persist' => false,
        'type' => self::MEAL_ADDRESS_OPTION,
    );

    /**
     * GeoAddressService constructor.
     *
     * @param Logger             $logger
     * @param EntityManager      $entityManager
     * @param Translator         $translator
     * @param ProviderAggregator $providerAggregator
     */
    public function __construct(
        Logger $logger,
        EntityManager $entityManager,
        Translator $translator,
        ProviderAggregator $providerAggregator
    ) {
        $this->providerAggregator = $providerAggregator;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->geoCoder = $providerAggregator;
    }

    public function updateEntityByLocation(MealAddress $mealAddress, string $location): GeoAddressServiceData
    {
        $location = str_replace(',', '', $location);

        $serviceData = $this->getMealAddressByLocation($location);

        $this->create(true, $serviceData);

        return $serviceData;
    }

    public function createMealAddressByLocation(string $location, $persist = false): GeoAddressServiceData
    {
        // normalizeLocationString
        $location = $this->normalizeLocationString($location);

        // returns serviceData with a new (empty but locationString) MealAddress
        // OR
        // an existing and filled MealAddress that matches this locationString.
        $serviceData = $this->getMealAddressByLocation($location);

        // Add GeoCoding data ...
        try {
            $this->geocode($location, $serviceData);
            $this->create($persist, $serviceData);
        } catch (GeoCodeException $geoCodeException) {
            $this->logger->addError($geoCodeException->getMessage());
            $serviceData->addError($geoCodeException->getMessage());
            $serviceData->setValidity(false);
        }

        return $serviceData;
    }

    public function createByLocationOptions(string $location, array $options = null): GeoAddressServiceData
    {
        if (null === $options) {
            array_merge(self::$defaultOptions, $options);
        }

        $persist = false;
        $addressType = $options['type'];
        // Normalize ...
        $location = $this->normalizeLocationString($location);

        switch ($addressType) {
            case self::MEAL_ADDRESS_OPTION:
                $mealAddress = new MealAddress();
                $mealAddress->setLocationString($location);
                $serviceData = new GeoAddressServiceData($mealAddress);
                break;
            case self::RESTAURANT_ADDRESS_OPTION:
                $restaurantAddress = new RestaurantAddress();
                $restaurantAddress->setLocationString($location);
                $serviceData = new GeoAddressServiceData($restaurantAddress);
                break;
            default:
                $this->logger->addError('Unknown option: '.$addressType);
                break;
        }

        try {
            $this->geocode($location, $serviceData);
            if ($persist) {
                if ($serviceData->isValid()) {
                    $entity = $serviceData->getEntity($serviceData->getSpecification());
                    $this->entityManager->persist($entity);
                    $this->entityManager->flush();
                    $serviceData->setData($serviceData->getSpecification(), $entity);

                    $copyId = $this->sameAddressCopy($serviceData);
                    $serviceData->setData('sameAddressID', $copyId);
                }
            }
        } catch (GeoCodeException $geoCodeException) {
            $this->logger->addError($geoCodeException->getMessage());
            $serviceData->addError($geoCodeException->getMessage());
            $serviceData->setValidity(false);
        } catch (ServiceDataException $serviceDataException) {
            $this->logger->addError($serviceDataException->getMessage());
            $serviceData->addError($serviceDataException->getMessage());
            $serviceData->setValidity(false);
        } catch (OptimisticLockException $optimisticLockException) {
            $this->logger->addError($optimisticLockException->getMessage());
            $serviceData->addError($optimisticLockException->getMessage());
            $serviceData->setValidity(false);
        } catch (ORMException $ORMException) {
            $this->logger->addError($ORMException->getMessage());
            $serviceData->addError($ORMException->getMessage());
            $serviceData->setValidity(false);
        }

        return $serviceData;
    }

    /**
     * Updates the given GeoCodeable Entity using the contained locationString
     * property.
     *
     * @param GeoCodeable $locationData an Entity implementing LocationData
     * @param bool        $persist      default true, persisting the geocodeable entity
     *
     * @return GeoAddressServiceData containing the result of the geo coding operation
     */
    public function updateGeoAddress(GeoCodeable $locationData, $persist = true): GeoAddressServiceData
    {
        $serviceData = new GeoAddressServiceData($locationData);
        try {
            $this->geocode($locationData->getLocationString(), $serviceData);
            if ($persist && $serviceData->isValid()) {
                $this->logger->addError('Persist!'.$locationData->getLocationString());
                $this->entityManager->persist($locationData);
                // $this->entityManager->flush();
            }
            if (!$serviceData->isValid()) {
                $this->logger->addError('Invalid!'.$locationData->getLocationString());
            }
        } catch (\Exception $exception) {
            $this->logger->addError($exception->getMessage());
            $serviceData->addError($exception->getMessage());
            $serviceData->setValidity(false);
        }

        return $serviceData;
    }

    public function isSynced(MealAddress $mealAddress): bool
    {
        $locationString = $mealAddress->getLocationString();
        try {
            $streetFound = strpos($locationString, $mealAddress->getStreetName());
            $streetNumberFound = strpos($locationString, $mealAddress->getStreetNumber());
            $cityFound = strpos($locationString, $mealAddress->getCity());
            $postalCodeFound = strpos($locationString, $mealAddress->getPostalCode());
        } catch (\Error $err) {
            return false;
        }

        if ($streetFound > 1) {
            if ($streetNumberFound > 1) {
                if ($cityFound > 1) {
                    if ($postalCodeFound > 1) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Searches for an existing "location" and returns an existing (managed) MealAddress or a new one (!managed).
     *
     * @param string $location the location to geocode
     *
     * @return GeoAddressServiceData the address without geocoding
     */
    public function getMealAddressByLocation(string $location): GeoAddressServiceData
    {
        $mealAddress = $this->entityManager->getRepository('ApiBundle:Meal\MealAddress')->findOneBy(
            array('locationString' => $location)
        )
        ;
        if (null === $mealAddress) {
            $mealAddress = new MealAddress();
            $mealAddress->setLocationString($location);
        }

        return new GeoAddressServiceData($mealAddress);
    }

    /**
     * Searches for an existing "location" and returns an existing (managed) RestaurantAddress or a new one (!managed).
     *
     * @param string $location the location to geocode
     *
     * @return GeoAddressServiceData the address without geocoding
     */
    public function getRestaurantAddressByLocation(string $location): GeoAddressServiceData
    {
        $restaurantAddress = $this->entityManager->getRepository('MMUserBundle:RestaurantAddress')->findOneBy(
            array('locationString' => $location)
        )
        ;
        if (null === $restaurantAddress) {
            $restaurantAddress = new RestaurantAddress();
            $restaurantAddress->setLocationString($location);
        }

        return new GeoAddressServiceData($restaurantAddress);
    }

    public function update(GeoAddressServiceData $serviceData)
    {
        if ($serviceData->isValid()) {
            $this->entityManager->persist($serviceData->getEntity($serviceData->getSpecification()));
        }
    }

    public function restore(int $Id): GeoAddressServiceData
    {
        throw new NotImplementedException('This has not been implemented yet!');
    }

    public function getAddressServiceData(GeoCodeable $geoCodeable): GeoAddressServiceData
    {
        return new GeoAddressServiceData($geoCodeable);
    }

    /**
     * Used to copy a RestaurantAddress to a MealAddress.
     *
     * @param RestaurantAddress $restaurantAddress
     *
     * @return MealAddress
     */
    public function copyToMealAddress(RestaurantAddress $restaurantAddress): MealAddress
    {
        $srcAddress = new ReflectionObject($restaurantAddress);
        $targetAddress = new ReflectionObject(new MealAddress());
        $copyAddress = new MealAddress();

        foreach ($srcAddress->getProperties() as $property) {
            if ($targetAddress->hasProperty($property->getName())) {
                if ('id' !== $property->getName() &&
                    'hash' !== $property->getName()) {
                    $getMethod = $srcAddress->getMethod('get'.ucfirst($property->getName()));
                    $getMethod->setAccessible(true);
                    $getResult = $getMethod->invoke($restaurantAddress);
                    if (null !== $getResult) {
                        $setMethod = $targetAddress->getMethod('set'.ucfirst($property->getName()));
                        $setMethod->setAccessible(true);
                        $setMethod->invoke($copyAddress, $getResult);
                        $this->logger->addDebug('Copy<->: '.$property->getName());
                    }
                }
            }
        }

        return $copyAddress;
    }

    /**
     * The real geocoding work using the provider aggregator, result inlcuding errors are found
     * in GeoAddressServiceData.
     *
     * @param string                $locationString
     * @param GeoAddressServiceData $serviceData
     *
     * @throws GeoCodeException
     */
    private function geocode(string $locationString, GeoAddressServiceData $serviceData)
    {
        $this->logger->debug(basename(__METHOD__.' locationString:'.$locationString));
        try {
            /** @var Collection $addressCollection */
            $addressCollection = $this->providerAggregator->geocode($locationString);
            if (0 === $addressCollection->count()) {
                $serviceData->addError('No GeoLocations found!');
                $serviceData->setValidity(false);

                return;
            }
            /** @var array|Location[] */
            $allGoogleAddressResults = $this->providerAggregator->geocode($locationString)->all();
            if (\count($allGoogleAddressResults) > 1) {
                // we still add all results ...
                $this->addGeoLocationsToServiceData($allGoogleAddressResults, $serviceData);
                $serviceData->addError('To many GeoLocations found!');
            }
        } catch (Exception $exception) {
            // Something inside GeoCoder went wrong ...
            $serviceData->addError('Geocoding failed!');
            $serviceData->addError($exception->getMessage());
            $serviceData->setValidity(false);

            throw new GeoCodeException(
                $locationString,
                sprintf('Geocoding for %s failed!', $locationString),
                $exception->getCode(),
                $exception
            );
        }
        /** @var GoogleAddress $googleAddress */
        $googleAddress = $allGoogleAddressResults[0];
        try {
            $this->addGoogleAddressToServiceData($googleAddress, $serviceData);
            $serviceData->setValidity(true);
        } catch (InvalidArgumentException $invalidArgumentException) {
            $this->addGeoLocationsToServiceData($allGoogleAddressResults, $serviceData);
            $serviceData->addError('Geocoding failed!');
            $serviceData->addError($invalidArgumentException->getMessage());
            $serviceData->setValidity(false);
        } catch (ServiceDataException $serviceDataException) {
            $this->addGeoLocationsToServiceData($allGoogleAddressResults, $serviceData);
            $serviceData->addError('Geocoding failed!');
            $serviceData->addError($serviceDataException->getMessage());
            $serviceData->setValidity(false);
        }
    }

    private function addGeoLocationsToServiceData($allResults, GeoAddressServiceData $serviceData)
    {
        $serviceData->setData('GeoLocations', $allResults);
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param GoogleAddress         $googleAddress
     * @param GeoAddressServiceData $serviceData
     *
     * @throws ServiceDataException
     * @throws InvalidArgumentException
     */
    private function addGoogleAddressToServiceData(GoogleAddress $googleAddress, GeoAddressServiceData $serviceData)
    {
        /** @var MealAddress $mealAddress */
        $mealAddress = $serviceData->getEntity($serviceData->getSpecification());
        $mealAddress->setBounds($googleAddress->getBounds()->toArray());
        $mealAddress->setCity($this->failIfNull(
            $googleAddress->getLocality(), 'city')
        );
        $mealAddress->setPostalCode($this->getEmptyIfNull($googleAddress->getPostalCode()));
        $mealAddress->setCountry($this->getEmptyIfNull($googleAddress->getCountry()->getName()));
        $mealAddress->setCountryCode($this->getEmptyIfNull($googleAddress->getCountry()->getCode()));
        $mealAddress->setLocality($this->getEmptyIfNull($googleAddress->getLocality()));
        $mealAddress->setSublocality($this->getEmptyIfNull($googleAddress->getSubLocality()));
        $mealAddress->setStreetName($this->failIfNull($googleAddress->getStreetName(), 'streetname'));
        $mealAddress->setStreetNumber($this->failIfNull($googleAddress->getStreetNumber(), 'streetnumber'));

        $point = new Point(
            $googleAddress->getCoordinates()->getLatitude(),
            $googleAddress->getCoordinates()->getLongitude()
        );

        $mealAddress->setLocation($point);

        if ($googleAddress->getAdminLevels()->count() > 0) {
            foreach ($googleAddress->getAdminLevels()->all() as $adminLevel) {
                if (1 === $adminLevel->getLevel()) {
                    $mealAddress->setState($this->failIfNull($adminLevel->getName(), 'state'));
                }
            }
        } else {
            // force fail ...
            $mealAddress->setState($this->failIfNull(null, 'state'));
        }

        // Formatted address to locationString
        $mealAddress->setLocationString($googleAddress->getFormattedAddress());

        // @todo ... should be grap more data from google? *evil grin

        $serviceData->setData('GeoLocation', $googleAddress);
    }

    private function addGeoAddressToServiceData(GeoAddress $address, GeoAddressServiceData $serviceData)
    {
        /** @var MealAddress $mealAddress */
        $mealAddress = $serviceData->getEntity($serviceData->getSpecification());

        $mealAddress->setBounds($address->getBounds()->toArray());
        $mealAddress->setCity($this->failIfNull($address->getLocality(), 'city'));
        $mealAddress->setPostalCode($this->getEmptyIfNull($address->getPostalCode()));
        $mealAddress->setCountry($this->getEmptyIfNull($address->getCountry()->getName()));
        $mealAddress->setCountryCode($this->getEmptyIfNull($address->getCountryCode()));
        $mealAddress->setLocality($this->getEmptyIfNull($address->getLocality()));
        $mealAddress->setSublocality($this->getEmptyIfNull($address->getSubLocality()));
        $mealAddress->setStreetName($this->failIfNull($address->getStreetName(), 'streetname'));
        $mealAddress->setStreetNumber($this->failIfNull($address->getStreetNumber(), 'streetnumber'));

        $point = new Point($address->getLatitude(), $address->getLongitude());
        $mealAddress->setLocation($point);

        if ($address->getAdminLevels()->count() > 0) {
            foreach ($address->getAdminLevels()->all() as $adminLevel) {
                if (1 === $adminLevel->getLevel()) {
                    $mealAddress->setState($this->failIfNull($adminLevel->getName(), 'state'));
                }
            }
        } else {
            // force fail ...
            $mealAddress->setState($this->failIfNull(null, 'state'));
        }

        // Now this is interesting ...
        $mealAddress->setLocationString(
            $mealAddress->getStreetName().' '
            .$mealAddress->getStreetNumber().', '
            .$mealAddress->getPostalCode().' '
            .$mealAddress->getCity());

        $serviceData->setData('GeoLocation', $address);
    }

    private function failIfNull($check, $checkName)
    {
        if (null === $check) {
            throw new InvalidArgumentException($this->translator->trans('mealmatch.geocode.failed.'.$checkName.'.isempty', array(), 'Mealmatch'));
        }

        return $check;
    }

    private function getEmptyIfNull($check)
    {
        if (null === $check) {
            return ApiConstants::EMPTY_STRING;
        }

        return $check;
    }

    private function sameAddressCopy(GeoAddressServiceData $serviceData)
    {
        $sameLoc =
            $serviceData->getMealAddress()->getCountry().'_'.
            $serviceData->getMealAddress()->getPostalCode().'_'.
            $serviceData->getMealAddress()->getCity().'_'.
            $serviceData->getMealAddress()->getStreetName().'_'.
            $serviceData->getMealAddress()->getStreetNumber();

        $copyID = null;
        $foundSameAddress = $this->entityManager->getRepository('ApiBundle:SameAddress')->findOneBy(
            array(
                'combinedLocationString' => $sameLoc,
            )
        )
        ;

        if (null === $foundSameAddress) {
            $sameAddress = new SameAddress();
            $sameAddress->setCombinedLocationString($sameLoc);
            $sameAddress->addMealAddress($serviceData->getMealAddress());
            $this->entityManager->persist($sameAddress);
            $copyID = $sameAddress->getId();
        } else {
            $foundSameAddress->addMealAddress($serviceData->getMealAddress());
            $this->entityManager->persist($foundSameAddress);
            $copyID = $foundSameAddress->getId();
        }

        $this->entityManager->flush();

        return $copyID;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param        $persist
     * @param        $serviceData
     * @param string $myArgument  with a *description* of this argument, these may also
     *                            span multiple lines
     */
    private function create($persist, GeoAddressServiceData $serviceData): void
    {
        if (!$serviceData->isManaged($serviceData->getSpecification())) {
            if ($persist) {
                if ($serviceData->isValid()) {
                    $this->logger->debug(basename(__METHOD__.' persist!'));
                    $entity = $serviceData->getEntity($serviceData->getSpecification());
                    $this->entityManager->persist($entity);
                    $this->entityManager->flush();
                    $serviceData->setData($serviceData->getSpecification(), $entity);

                    $copyId = $this->sameAddressCopy($serviceData);
                    $serviceData->setData('sameAddressID', $copyId);
                }
            }
        } else {
            // it's managed ...
            $serviceData->setValidity(true);
            $this->logger->debug(basename(__METHOD__.' its managed!'));
        }
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $location
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return mixed|string
     */
    private function normalizeLocationString(string $location)
    {
        $location = str_replace(',', '', $location);

        return $location;
    }
}
