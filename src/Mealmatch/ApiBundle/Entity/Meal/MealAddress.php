<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Entity\Meal;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Geocodable\Geocodable;
use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;
use Mealmatch\ApiBundle\Entity\AbstractEntity;
use Mealmatch\ApiBundle\Entity\GeoCodeable;

/**
 * @ORM\Entity(repositoryClass="Mealmatch\ApiBundle\Repository\MealAddressRepository")
 */
class MealAddress extends AbstractEntity implements GeoCodeable
{
    /*
     * TRAITS
     */
    use Geocodable;

    /**
     * @var string
     *
     * @ORM\Column(name="location_string", type="string", length=255)
     */
    private $locationString = '-';

    /**
     * @var string
     *
     * @ORM\Column(name="bell_sign", type="string", length=30)
     */
    private $bellSign = '-';

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="countryCode", type="string", length=5, nullable=true)
     */
    private $countryCode;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=255, nullable=true)
     */
    private $state;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    private $city = '-';

    /**
     * @var string
     *
     * @ORM\Column(name="postalCode", type="string", length=255, nullable=true)
     */
    private $postalCode = '-';

    /**
     * @var string
     *
     * @ORM\Column(name="streetName", type="string", length=255, nullable=true)
     */
    private $streetName;

    /**
     * @var string
     *
     * @ORM\Column(name="streetNumber", type="string", length=255, nullable=true)
     */
    private $streetNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="extraLine1", type="string", length=255, nullable=true)
     */
    private $extraLine1;

    /**
     * @var string
     *
     * @ORM\Column(name="extraLine2", type="string", length=255, nullable=true)
     */
    private $extraLine2;

    /**
     * @var string
     *
     * @ORM\Column(name="locality", type="string", length=255, nullable=true)
     */
    private $locality;

    /**
     * @var string
     *
     * @ORM\Column(name="sublocality", type="string", length=255, nullable=true)
     */
    private $sublocality;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var array
     * @ORM\Column(name="bounds", type="array", nullable=true);
     */
    private $bounds;

    public function __construct()
    {
        // initHash if not hashed yet ...
        if (!$this->isHashed()) {
            $this->initHash();
        }
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return string
     */
    public function __toString()
    {
        $allProps = implode(
            ', ',
            array_map(
                function ($v, $k) {
                    $trimPos = \strlen(__CLASS__) + 1;
                    $x = substr($k, $trimPos, 40);
                    if ($v instanceof \DateTime) {
                        $v = $v->format('H:i');
                    }
                    if (\is_array($v)) {
                        $v = implode(', ', $v);
                    }

                    return sprintf("%s='%s'", $x, $v);
                },
                $this->toArray(),
                array_keys($this->toArray())
            )
        );

        return __CLASS__.$allProps;
    }

    /**
     * @return string|null
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     *
     * @return MealAddress
     */
    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     *
     * @return MealAddress
     */
    public function setCountryCode(string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     *
     * @return MealAddress
     */
    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCity()
    {
        if (null === $this->city) {
            $this->city = '-';
        }

        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return MealAddress
     */
    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPostalCode()
    {
        if (null === $this->postalCode) {
            $this->postalCode = '-';
        }

        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     *
     * @return MealAddress
     */
    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStreetName()
    {
        return $this->streetName;
    }

    /**
     * @param string $streetName
     *
     * @return MealAddress
     */
    public function setStreetName(string $streetName): self
    {
        $this->streetName = $streetName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStreetNumber()
    {
        return $this->streetNumber;
    }

    /**
     * @param string $streetNumber
     *
     * @return MealAddress
     */
    public function setStreetNumber(string $streetNumber): self
    {
        $this->streetNumber = $streetNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getExtraLine1(): string
    {
        if (null === $this->extraLine1) {
            $this->setExtraLine1('---');
        }

        return $this->extraLine1;
    }

    /**
     * @param string $extraLine1
     *
     * @return MealAddress
     */
    public function setExtraLine1(string $extraLine1): self
    {
        $this->extraLine1 = $extraLine1;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getExtraLine2()
    {
        return $this->extraLine2;
    }

    /**
     * @param string $extraLine2
     *
     * @return MealAddress
     */
    public function setExtraLine2(string $extraLine2): self
    {
        $this->extraLine2 = $extraLine2;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocality()
    {
        return $this->locality;
    }

    /**
     * @param string $locality
     *
     * @return MealAddress
     */
    public function setLocality(string $locality): self
    {
        $this->locality = $locality;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSublocality()
    {
        return $this->sublocality;
    }

    /**
     * @param string $sublocality
     *
     * @return MealAddress
     */
    public function setSublocality(string $sublocality): self
    {
        $this->sublocality = $sublocality;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return MealAddress
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocationString(): string
    {
        if (null === $this->locationString) {
            $this->locationString = '-';
        }

        return $this->locationString;
    }

    /**
     * @param string $locationString
     *
     * @return MealAddress
     */
    public function setLocationString(string $locationString): self
    {
        $this->locationString = $locationString;

        return $this;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @return array
     */
    public function getCoordinates(): array
    {
        return array($this->getLocation()->getLatitude(), $this->getLocation()->getLongitude());
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return float|int
     */
    public function getLatitude()
    {
        return $this->getLocation()->getLatitude();
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return float|int
     */
    public function getLongitude()
    {
        return $this->getLocation()->getLongitude();
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *.
     *
     * @return array
     */
    public function getBounds(): array
    {
        return $this->bounds;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param array $pBounds
     */
    public function setBounds(array $pBounds)
    {
        $this->bounds = $pBounds;
    }

    /**
     * @return string|null
     */
    public function getBellSign()
    {
        return $this->bellSign;
    }

    /**
     * @param string $bellSign
     *
     * @return MealAddress
     */
    public function setBellSign(string $bellSign): self
    {
        $this->bellSign = $bellSign;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @return string|null
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Get location.
     *
     * @return point
     */
    public function getLocation()
    {
        return $this->location;
    }

    public function isGeoCoded(): bool
    {
        return null === $this->getLocation() ? false : true;
    }

    /**
     * @todo: Finish PHPDoc! Move this logic out of the entity!!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return array
     */
    private function toArray(): array
    {
        $props = array();
        foreach ((array) $this as $key => $value) {
            $props[$key] = $value;
        }

        return $props;
    }
}
