<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Mealmatch\ApiBundle\Entity\AbstractEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * MMUserProfile.
 *
 * @ORM\Table(name="m_m_user_profile")
 * @ORM\Entity(repositoryClass="MMUserBundle\Repository\MMUserProfileRepository")
 */
class MMUserProfile extends AbstractEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="payPalEmail", type="string", length=255, nullable=true)
     */
    private $payPalEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="selfDescription", type="text", nullable=true)
     */
    private $selfDescription;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Assert\Image(
     *     minWidth = 90,
     *     minHeight = 90,
     *     detectCorrupted = true,
     *     corruptedMessage = "Die Datei konnte nicht als Bild erkannt werden",
     *     maxSize = "4m",
     *     uploadIniSizeErrorMessage = "Die maximale Dateigröße für das Profilbild ist 4 MB!",
     *     uploadFormSizeErrorMessage = "Die maximale Dateigröße ist 4 MB!"
     * )
     *
     * @var File
     */
    private $imageFile;

    /**
     * The profile image Name will create an object in S3.
     *
     * https://mealmatch-stage.objects.frb.io/image/u{$imageName}.png
     * The default image is already there->
     * https://mealmatch-stage.objects.frb.io/image/udefault.png
     *
     * @ORM\Column(name="imageName", type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $imageName = 'image/udefault.png';

    /**
     * @ORM\Column(name="firstName", type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $firstName;
    /**
     * @ORM\Column(name="lastName", type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $lastName;

    /**
     * @ORM\Column(name="postalAddress", type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $postalAddress;
    /**
     * @ORM\Column(name="addressLine1", type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $addressLine1;
    /**
     * @ORM\Column(name="addressLine2",type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $addressLine2;
    /**
     * @ORM\Column(name="areaCode", type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $areaCode;
    /**
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $city;
    /**
     * @ORM\Column(name="state", type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $state;
    /**
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $country;
    /**
     * @ORM\Column(name="nationality", type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $nationality;
    /**
     * @ORM\Column(name="birthday", type="date", nullable=true)
     *
     * @var \DateTime
     */
    private $birthday;

    /**
     * @ORM\Column(name="gender", type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $gender;

    /**
     * @ORM\Column(name="hobbies", type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $hobbies;

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     */
    public function __toString()
    {
        return (string) 'UserProfile::'.$this->getId();
    }

    /**
     * Get payPalEmail.
     *
     * @return string
     */
    public function getPayPalEmail()
    {
        return $this->payPalEmail;
    }

    /**
     * Set payPalEmail.
     *
     * @param string $payPalEmail
     *
     * @return MMUserProfile
     */
    public function setPayPalEmail($payPalEmail)
    {
        $this->payPalEmail = $payPalEmail;

        return $this;
    }

    /**
     * Get phone.
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set phone.
     *
     * @param string $phone
     *
     * @return MMUserProfile
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get selfDescription.
     *
     * @return string
     */
    public function getSelfDescription()
    {
        return $this->selfDescription;
    }

    /**
     * Set selfDescription.
     *
     * @param string $selfDescription
     *
     * @return MMUserProfile
     */
    public function setSelfDescription($selfDescription)
    {
        $this->selfDescription = $selfDescription;

        return $this;
    }

    /**
     * @return File|null
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $image
     *
     * @return MMUserProfile
     */
    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        if ($image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTime('now');
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getImageName()
    {
        return $this->imageName;
    }

    /**
     * @param string $imageName
     *
     * @return MMUserProfile
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     *
     * @return MMUserProfile
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     *
     * @return MMUserProfile
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    /**
     * @param mixed $addressLine1
     *
     * @return MMUserProfile
     */
    public function setAddressLine1($addressLine1)
    {
        $this->addressLine1 = $addressLine1;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddressLine2()
    {
        return $this->addressLine2;
    }

    /**
     * @param mixed $addressLine2
     *
     * @return MMUserProfile
     */
    public function setAddressLine2($addressLine2)
    {
        $this->addressLine2 = $addressLine2;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAreaCode()
    {
        return $this->areaCode;
    }

    /**
     * @param mixed $areaCode
     *
     * @return MMUserProfile
     */
    public function setAreaCode($areaCode)
    {
        $this->areaCode = $areaCode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     *
     * @return MMUserProfile
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     *
     * @return MMUserProfile
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     *
     * @return MMUserProfile
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAge()
    {
        if (null !== $this->birthday) {
            $end = new DateTime('now');
            $interval = $end->diff($this->birthday);

            return $interval->y;
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param mixed $gender
     *
     * @return MMUserProfile
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHobbies()
    {
        return $this->hobbies;
    }

    /**
     * @param mixed $hobbies
     *
     * @return MMUserProfile
     */
    public function setHobbies($hobbies)
    {
        $this->hobbies = $hobbies;

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

    /**
     * @return string|null
     */
    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    /**
     * @param string $nationality
     *
     * @return MMUserProfile
     */
    public function setNationality(string $nationality): self
    {
        $this->nationality = $nationality;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostalAddress()
    {
        return $this->postalAddress;
    }

    /**
     * @param string $postalAddress
     *
     * @return MMUserProfile
     */
    public function setPostalAddress(string $postalAddress): self
    {
        $this->postalAddress = $postalAddress;

        return $this;
    }
}
