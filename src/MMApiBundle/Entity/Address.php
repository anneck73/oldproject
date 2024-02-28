<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;

/**
 * Address.
 *
 * @ORM\Table(name="address")
 * @ORM\Entity(repositoryClass="MMApiBundle\Repository\AddressRepository")
 */
class Address
{
    /*
     * TRAITS
     */
    use ORMBehaviors\Geocodable\Geocodable;
    use
        ORMBehaviors\Sortable\Sortable;
    use
        ORMBehaviors\Timestampable\Timestampable;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="location_address", type="string", length=255)
     */
    private $locationAddress = '-';

    /**
     * One Address has one Meal.
     *
     * @todo: Finish PHPDoc!
     *
     * @var
     * @ORM\OneToOne(targetEntity="Meal", inversedBy="address");
     */
    private $meal;

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
     * @ORM\Column(name="exraLine2", type="string", length=255, nullable=true)
     */
    private $exraLine2;
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
     * @ORM\Column(name="Description", type="string", length=255, nullable=true)
     */
    private $description;

    public function __toString()
    {
        $allProps = implode(
            ', ',
            array_map(
                function ($v, $k) {
                    $trimPos = \strlen(__CLASS__) + 1;
                    $x = substr($k, $trimPos, 20);
                    if ($v instanceof \DateTime) {
                        $v = $v->format('H:mm');
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
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @param string $country
     *
     * @return Address
     */
    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     *
     * @return Address
     */
    public function setCountryCode(string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @param string $state
     *
     * @return Address
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
     * @return Address
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
     * @return Address
     */
    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getStreetName(): string
    {
        return $this->streetName;
    }

    /**
     * @param string $streetName
     *
     * @return Address
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
     * @return Address
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
        return $this->extraLine1;
    }

    /**
     * @param string $extraLine1
     *
     * @return Address
     */
    public function setExtraLine1(string $extraLine1): self
    {
        $this->extraLine1 = $extraLine1;

        return $this;
    }

    /**
     * @return string
     */
    public function getExraLine2(): string
    {
        return $this->exraLine2;
    }

    /**
     * @param string $exraLine2
     *
     * @return Address
     */
    public function setExraLine2(string $exraLine2): self
    {
        $this->exraLine2 = $exraLine2;

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
     * @return Address
     */
    public function setLocality(string $locality): self
    {
        $this->locality = $locality;

        return $this;
    }

    /**
     * @return string
     */
    public function getSublocality(): string
    {
        return $this->sublocality;
    }

    /**
     * @param string $sublocality
     *
     * @return Address
     */
    public function setSublocality(string $sublocality): self
    {
        $this->sublocality = $sublocality;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Address
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMeal()
    {
        return $this->meal;
    }

    /**
     * @param mixed $meal
     *
     * @return Address
     */
    public function setMeal($meal)
    {
        $this->meal = $meal;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocationAddress(): string
    {
        if (null === $this->locationAddress) {
            $this->locationAddress = '-';
        }

        return $this->locationAddress;
    }

    /**
     * @param string $locationAddress
     *
     * @return Address
     */
    public function setLocationAddress(string $locationAddress): self
    {
        $this->locationAddress = $locationAddress;

        return $this;
    }

    /**
     * @return Point
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param Point $location
     */
    public function setLocation(Point $location)
    {
        $this->location = $location;
    }

    private function toArray()
    {
        $props = array();
        foreach ((array) $this as $key => $value) {
            $props[$key] = $value;
        }

        return $props;
    }
}
