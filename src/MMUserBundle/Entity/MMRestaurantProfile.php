<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mealmatch\ApiBundle\Entity\AbstractEntity;
use Mealmatch\ApiBundle\Entity\Restaurant\RestaurantAddress;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The MMRestaurantProfile Entity.
 *
 * @ORM\Table(name="mm_restaurant_profile")
 * @ORM\Entity(repositoryClass="MMUserBundle\Repository\MMRestaurantProfileRepository")
 */
class MMRestaurantProfile extends AbstractEntity
{
    /**
     * A Restaurant as possibly more than one address.
     *
     * @var Collection
     * @ORM\ManyToMany(targetEntity="Mealmatch\ApiBundle\Entity\Restaurant\RestaurantAddress", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinTable(name="restaurant_profile_to_restaurant_address",
     *      joinColumns={@ORM\JoinColumn(name="restaurant_profile_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="restaurant_address_id", referencedColumnName="id")}
     *      )
     */
    protected $addresses;

    /**
     * The legal name of the company, or the owner (Inhaber).
     *
     * @var string|null
     *
     * @Assert\NotBlank()

     * @ORM\Column(name="company", type="string", length=128, nullable=true)
     */
    private $company;

    /**
     * The name of the restaurant.
     *
     * @var string|null
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="name", type="string", length=128, nullable=true)
     */
    private $name;

    /**
     * The type of the restaurant.
     *
     * @var string|null
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="type", type="string", length=128, nullable=true)
     */
    private $type;

    /**
     * A collection of legal files supplied by the restaurant.
     *
     * @var Collection a collection of RestaurantFiles
     * @ORM\OneToMany(targetEntity="MMUserBundle\Entity\RestaurantFile", mappedBy="restaurantProfile")
     */
    private $legalFiles;

    /**
     * The commercial register number.
     *
     * @var string|null
     * @ORM\Column(name="commercialRegisterNumber", type="string", length=128, nullable=true)
     */
    private $commercialRegisterNumber;

    /**
     * The tax id.
     *
     * @var string|null
     * @ORM\Column(name="taxID", type="string", length=128, nullable=true)
     */
    private $taxID;

    /**
     * The value of the tax applied to all MealTickets for this Restaurant.
     *
     * @var float
     * @ORM\Column(name="tax_rate", type="float")
     */
    private $taxRate = 19.0;

    /**
     * The person who is the represntative.
     *
     * @var string|null
     * @ORM\Column(name="authorizedRepresentative", type="string", length=128, nullable=true)
     */
    private $authorizedRepresentative;

    /**
     * The email you use for PayPal.
     *
     * @var string|null
     * @ORM\Column(name="payPalEmail", type="string", length=128, nullable=true)
     */
    private $payPalEmail;

    /**
     * The default currency to be used when creating restaurant meals.
     *
     * @var string
     * @ORM\Column(name="defaultCurrency", type="string", length=3)
     */
    private $defaultCurrency = 'EUR';

    /**
     * The Bankaccountnumber. E. g. IBAN & SWIFT-BIC in Europe.
     *
     * @var string|null
     * @ORM\Column(name="bankIBAN", type="string", length=128, nullable=true)
     */
    private $bankIBAN;

    /**
     * The BIC.
     *
     * @var string|null
     * @ORM\Column(name="bankBIC", type="string", length=128, nullable=true)
     */
    private $bankBIC;

    /**
     * Legal contact Address.
     *
     * @var string|null
     * @ORM\Column(name="contactAddress", type="text", length=500, nullable=true)
     */
    private $contactAddress;

    /**
     * Phonenumber in Intl. format e.g. +491234012345.
     *
     * @var string|null
     * @ORM\Column(name="contactPhone", type="string", length=128, nullable=true)
     */
    private $contactPhone;

    /**
     * The email address.
     *
     * @var string|null
     * @ORM\Column(name="contactEmail", type="string", length=128, nullable=true)
     */
    private $contactEmail;

    /**
     * The locationString is a special string used by MealAddress and the MealAddressService to automagically
     * create geo-coordinates from the given string. Example "Petersburgerstraße 69, Berlin".
     *
     * @var string|null the locationString used by MealAddress
     * @ORM\Column(name="locationString", type="string", length=150, nullable=true)
     * @
     */
    private $locationString;

    /**
     * AddressLine1 used in RestaurantProfile for creating MangopayID's.
     *
     * @var string|null the addressLine1 used by RestaurantService for UserLegal/HQ
     * @ORM\Column(name="legalRepresentativeAddressLine1", type="string", length=128, nullable=true)
     */
    private $legalRepresentativeAddressLine1;

    /**
     * AddressLine2 used in RestaurantProfile for creating MangopayID's.
     *
     * @var string|null the addressLine2 used by RestaurantService for UserLegal/HQ
     * @ORM\Column(name="legalRepresentativeAddressLine2", type="string", length=128, nullable=true)
     */
    private $legalRepresentativeAddressLine2;

    /**
     * City used in RestaurantProfile for creating MangopayID'S.
     *
     * @var string|null the city used by RestaurantService for UserLegal/HQ
     * @ORM\Column(name="legalRepresentativeCity", type="string", length=128, nullable=true)
     */
    private $legalRepresentativeCity;

    /**
     * Region used in RestaurantProfile for creating MangopayID's.
     *
     * @var string|null the region useed by RestaurantService for UserLegal/HQ
     * @ORM\Column(name="legalRepresentativeRegion", type="string", length=128, nullable=true)
     */
    private $legalRepresentativeRegion;

    /**
     * PostalCode used in RestaurantProfile for creating MangopayID's.
     *
     * @var string|null the postalCode used by RestaurantService for UserLegal/HQ
     * @ORM\Column(name="legalRepresentativePostalCode", type="string", length=128, nullable=true)
     */
    private $legalRepresentativePostalCode;

    /**
     * Country used in RestaurantProfile for creating MangopayID's.
     *
     * @var string|null the country used by RestaurantService for UserLegal/HQ
     * @ORM\Column(name="country", type="string", length=128, nullable=true)
     */
    private $country;

    /**
     * Nationality used in RestaurantProfile for creating.
     *
     * @var string|null the nationality used by RestaurantService for UserLegal/HQ
     * @ORM\Column(name="nationality", type="string", length=128, nullable=true)
     */
    private $nationality;

    /**
     * @ORM\Column(name="birthday", type="date", nullable=true)
     *
     * @var \DateTime
     */
    private $birthday;

    /**
     * @ORM\Column(name="legalRepresentativeFirstName", type="string", length=128, nullable=true)
     *
     * @var string
     */
    private $legalRepresentativeFirstName;
    /**
     * @ORM\Column(name="legalRepresentativeLastName", type="string", length=128, nullable=true)
     *
     * @var string
     */
    private $legalRepresentativeLastName;

    /**
     * Description of the location.
     *
     * @var string|null
     * @ORM\Column(name="description", type="text", length=1000, nullable=true)
     */
    private $description;

    /**
     * The pictures supplied for the restaurant.
     *
     * @var Collection a collection of RestaurantImages
     * @ORM\OneToMany(targetEntity="MMUserBundle\Entity\RestaurantImage", mappedBy="restaurantProfile", cascade={"persist"})
     */
    private $pictures;

    public function __construct()
    {
        parent::__construct();
        $this->pictures = new ArrayCollection();
        $this->legalFiles = new ArrayCollection();
        $this->addresses = new ArrayCollection();
    }

    public function __toString()
    {
        return __CLASS__.$this->getId();
    }

    /**
     * @return string|null
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param string|null $company
     *
     * @return MMRestaurantProfile
     */
    public function setCompany($company): self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     *
     * @return MMRestaurantProfile
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     *
     * @return MMRestaurantProfile
     */
    public function setType($type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getLegalFiles(): Collection
    {
        return $this->legalFiles;
    }

    /**
     * @param Collection $legalFiles
     *
     * @return MMRestaurantProfile
     */
    public function setLegalFiles(Collection $legalFiles): self
    {
        $this->legalFiles = $legalFiles;

        return $this;
    }

    public function addLegalFile(RestaurantFile $restaurantFile): self
    {
        $this->legalFiles->add($restaurantFile);
        $restaurantFile->setRestaurantProfile($this);

        return $this;
    }

    public function removeLegalFile(RestaurantFile $restaurantFile): self
    {
        $this->legalFiles->remove($restaurantFile);
        $restaurantFile->setRestaurantProfile(null);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCommercialRegisterNumber()
    {
        return $this->commercialRegisterNumber;
    }

    /**
     * @param string|null $commercialRegisterNumber
     *
     * @return MMRestaurantProfile
     */
    public function setCommercialRegisterNumber($commercialRegisterNumber): self
    {
        $this->commercialRegisterNumber = $commercialRegisterNumber;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTaxID()
    {
        return $this->taxID;
    }

    /**
     * @param $taxID
     *
     * @return MMRestaurantProfile
     */
    public function setTaxID($taxID): self
    {
        $this->taxID = $taxID;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAuthorizedRepresentative()
    {
        return $this->authorizedRepresentative;
    }

    /**
     * @param string $authorizedRepresentative
     *
     * @return MMRestaurantProfile
     */
    public function setAuthorizedRepresentative($authorizedRepresentative): self
    {
        $this->authorizedRepresentative = $authorizedRepresentative;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPayPalEmail()
    {
        return $this->payPalEmail;
    }

    /**
     * @param $payPalEmail
     *
     * @return MMRestaurantProfile
     */
    public function setPayPalEmail($payPalEmail): self
    {
        $this->payPalEmail = $payPalEmail;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBankIBAN()
    {
        return $this->bankIBAN;
    }

    /**
     * @param $bankIBAN
     *
     * @return MMRestaurantProfile
     */
    public function setBankIBAN($bankIBAN): self
    {
        $this->bankIBAN = $bankIBAN;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getContactAddress()
    {
        return $this->contactAddress;
    }

    /**
     * @param $contactAddress
     *
     * @return MMRestaurantProfile
     */
    public function setContactAddress($contactAddress): self
    {
        $this->contactAddress = $contactAddress;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getContactPhone()
    {
        return $this->contactPhone;
    }

    /**
     * @param $contactPhone
     *
     * @return MMRestaurantProfile
     */
    public function setContactPhone($contactPhone): self
    {
        $this->contactPhone = $contactPhone;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getContactEmail()
    {
        return $this->contactEmail;
    }

    /**
     * @param $contactEmail
     *
     * @return MMRestaurantProfile
     */
    public function setContactEmail($contactEmail): self
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocationString()
    {
        return $this->locationString;
    }

    /**
     * @param $locationString
     *
     * @return MMRestaurantProfile
     */
    public function setLocationString($locationString): self
    {
        $this->locationString = $locationString;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    /**
     * Returns the FIRST element of the internal addresses collection.
     *
     * @return RestaurantAddress|null
     */
    public function getAddress(): ?RestaurantAddress
    {
        if (0 === $this->addresses->count()) {
            return null;
        }

        return $this->addresses->first();
    }

    public function hasAddress(): bool
    {
        return $this->addresses->count() > 0 ? true : false;
    }

    /**
     * @param Collection $addresses
     *
     * @return MMRestaurantProfile
     */
    public function setAddresses(Collection $addresses): self
    {
        $this->addresses = $addresses;

        return $this;
    }

    public function addAddress(RestaurantAddress $restaurantAddress)
    {
        if ($this->addresses->contains($restaurantAddress)) {
            return $this;
        }
        $this->addresses->add($restaurantAddress);

        return $this;
    }

    public function setAddress(RestaurantAddress $restaurantAddress): self
    {
        $this->addresses = new ArrayCollection(array($restaurantAddress));

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
     * @param $description
     *
     * @return MMRestaurantProfile
     */
    public function setDescription($description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    /**
     * @param Collection $pictures
     *
     * @return MMRestaurantProfile
     */
    public function setPictures(Collection $pictures): self
    {
        $this->pictures = $pictures;

        return $this;
    }

    public function addPicture(RestaurantImage $image): self
    {
        $image->setRestaurantProfile($this);
        $this->pictures->add($image);

        return $this;
    }

    public function removePicture(RestaurantImage $image): self
    {
        $image->setRestaurantProfile(null);
        $this->pictures->removeElement($image);

        return $this;
    }

    /**
     * @return float
     */
    public function getTaxRate(): float
    {
        return $this->taxRate;
    }

    /**
     * @param float $taxRate
     *
     * @return MMRestaurantProfile
     */
    public function setTaxRate(float $taxRate): self
    {
        $this->taxRate = $taxRate;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultCurrency(): string
    {
        return $this->defaultCurrency;
    }

    /**
     * @param string $defaultCurrency
     *
     * @return MMRestaurantProfile
     */
    public function setDefaultCurrency(string $defaultCurrency): self
    {
        $this->defaultCurrency = $defaultCurrency;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBankBIC(): ?string
    {
        return $this->bankBIC;
    }

    /**
     * @param string|null $bankBIC
     *
     * @return MMRestaurantProfile
     */
    public function setBankBIC(?string $bankBIC): self
    {
        $this->bankBIC = $bankBIC;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLegalRepresentativeAddressLine1(): ?string
    {
        return $this->legalRepresentativeAddressLine1;
    }

    /**
     * @param string|null $legalRepresentativeAddressLine1
     *
     * @return MMRestaurantProfile
     */
    public function setLegalRepresentativeAddressLine1(?string $legalRepresentativeAddressLine1): self
    {
        $this->legalRepresentativeAddressLine1 = $legalRepresentativeAddressLine1;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLegalRepresentativeAddressLine2(): ?string
    {
        return $this->legalRepresentativeAddressLine2;
    }

    /**
     * @param string|null $legalRepresentativeAddressLine2
     *
     * @return MMRestaurantProfile
     */
    public function setLegalRepresentativeAddressLine2(?string $legalRepresentativeAddressLine2): self
    {
        $this->legalRepresentativeAddressLine2 = $legalRepresentativeAddressLine2;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLegalRepresentativeCity(): ?string
    {
        return $this->legalRepresentativeCity;
    }

    /**
     * @param string|null $legalRepresentativeCity
     *
     * @return MMRestaurantProfile
     */
    public function setLegalRepresentativeCity(?string $legalRepresentativeCity): self
    {
        $this->legalRepresentativeCity = $legalRepresentativeCity;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLegalRepresentativeRegion(): ?string
    {
        return $this->legalRepresentativeRegion;
    }

    /**
     * @param string|null $legalRepresentativeRegion
     *
     * @return MMRestaurantProfile
     */
    public function setLegalRepresentativeRegion(?string $legalRepresentativeRegion): self
    {
        $this->legalRepresentativeRegion = $legalRepresentativeRegion;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLegalRepresentativePostalCode(): ?string
    {
        return $this->legalRepresentativePostalCode;
    }

    /**
     * @param string|null $legalRepresentativePostalCode
     *
     * @return MMRestaurantProfile
     */
    public function setLegalRepresentativePostalCode(?string $legalRepresentativePostalCode): self
    {
        $this->legalRepresentativePostalCode = $legalRepresentativePostalCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getLegalRepresentativeFirstName()
    {
        return $this->legalRepresentativeFirstName;
    }

    /**
     * @param string $legalRepresentativeFirstName
     *
     * @return MMRestaurantProfile
     */
    public function setLegalRepresentativeFirstName(string $legalRepresentativeFirstName): self
    {
        $this->legalRepresentativeFirstName = $legalRepresentativeFirstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLegalRepresentativeLastName()
    {
        return $this->legalRepresentativeLastName;
    }

    /**
     * @param string $legalRepresentativeLastName
     *
     * @return MMRestaurantProfile
     */
    public function setLegalRepresentativeLastName(string $legalRepresentativeLastName): self
    {
        $this->legalRepresentativeLastName = $legalRepresentativeLastName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string|null $country
     *
     * @return MMRestaurantProfile
     */
    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    /**
     * @param string|null $nationality
     *
     * @return MMRestaurantProfile
     */
    public function setNationality(?string $nationality): self
    {
        $this->nationality = $nationality;

        return $this;
    }

    /**
     * Set birthday.
     *
     * @param \DateTime|null $birthday
     */
    public function setBirthday(\DateTime $birthday = null): self
    {
        $this->birthday = $birthday ? clone $birthday : null;

        return $this;
    }

    /**
     * Get birthday.
     *
     * @return \DateTime|null
     */
    public function getBirthday(): ?\DateTime
    {
        return $this->birthday ? clone $this->birthday : null;
    }
}
