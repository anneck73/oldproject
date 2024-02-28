<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\MessageBundle\Model\ParticipantInterface;
use FOS\UserBundle\Model\User as BaseUser;
use Knp\DoctrineBehaviors\Model\Timestampable\Timestampable;
use Mealmatch\ApiBundle\Entity\Meal\BaseMeal;
use Mealmatch\ApiBundle\Entity\User\Profiles\CouponProfile;
use Mealmatch\ApiBundle\MealMatch\Traits\Hashable;
use Mealmatch\GameLogicBundle\User\GameUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The Mealmatch user entity.
 *
 *
 * @ORM\Entity
 * @ORM\Table(name="mm_user")
 */
class MMUser extends BaseUser implements ParticipantInterface, GameUserInterface
{
    /*
     * Traits
     */
    use Hashable;
    use
        Timestampable;
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** @ORM\Column(name="facebook_id", type="string", length=255, nullable=true) */
    protected $facebook_id;

    /** @ORM\Column(name="facebook_access_token", type="string", length=255, nullable=true) */
    protected $facebook_access_token;

    /** @ORM\Column(name="google_id", type="string", length=255, nullable=true) */
    protected $google_id;

    /** @ORM\Column(name="google_access_token", type="string", length=255, nullable=true) */
    protected $google_access_token;

    /**
     * Many Users are guest in BaseMeals.
     *
     * @ORM\ManyToMany(targetEntity="Mealmatch\ApiBundle\Entity\Meal\BaseMeal", inversedBy="guests")
     * @ORM\JoinTable(name="base_meal_guests")
     */
    private $attendingBaseMeals;

    /**
     * One MMUser has Many Meals as host.
     *
     * @ORM\OneToMany(targetEntity="Mealmatch\ApiBundle\Entity\Meal\BaseMeal", mappedBy="host")
     */
    private $hostingMeals;

    /**
     * A User is connected to tickets as a host.
     *
     * @ORM\OneToMany(targetEntity="Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket", mappedBy="host")
     */
    private $hostTickets;

    /**
     * A User is connected to tickets as a guest.
     *
     * @ORM\OneToMany(targetEntity="Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket", mappedBy="guest")
     */
    private $guestTickets;

    /**
     * Many Users have a Profile with details about them.
     *
     * @ORM\ManyToOne(targetEntity="MMUserProfile", cascade={"all"})
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id", onDelete="set null")
     */
    private $profile;

    /**
     * Many Users have UserSettings with details about them.
     *
     * @ORM\ManyToOne(targetEntity="MMUserSettings", cascade={"all"})
     * @ORM\JoinColumn(name="settings_id", referencedColumnName="id", onDelete="set null")
     *
     * @var MMUserSettings
     */
    private $settings;

    /**
     * Many Users have a RestaurantProfile with details about their restaurant.
     *
     * @ORM\ManyToOne(targetEntity="MMRestaurantProfile", cascade={"all"})
     * @ORM\JoinColumn(name="restaurant_profile_id", referencedColumnName="id", onDelete="set null")
     *
     * @var MMRestaurantProfile
     */
    private $restaurantProfile;

    /**
     * Many Users have a PaymentProfile with details about their payment preferences and details.
     *
     * @ORM\ManyToOne(targetEntity="MMUserPaymentProfile", cascade={"all"})
     * @ORM\JoinColumn(name="user_payment_profile_id", referencedColumnName="id", onDelete="set null")
     *
     * @var MMUserPaymentProfile
     */
    private $paymentProfile;

    /**
     * Many Users have a CouponProfile with details about coupons they have used.
     *
     * @ORM\ManyToOne(targetEntity="Mealmatch\ApiBundle\Entity\User\Profiles\CouponProfile", cascade={"all"})
     * @ORM\JoinColumn(name="user_coupon_profile_id", referencedColumnName="id", onDelete="set null")
     *
     * @var CouponProfile
     */
    private $coupontProfile;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var bool
     * @ORM\Column(name="terms", type="boolean")
     * @Assert\IsTrue(message = "terms.not_true")
     */
    private $termsAccepted = false;

    /**
     * @todo: Finish PHPDoc!
     *
     * @var bool
     * @ORM\Column(name="over18", type="boolean")
     * @Assert\IsTrue(message = "over18.not_true")
     */
    private $over18 = true;

    /**
     * The overall kyc status of the user
     * No Document Submitted | Pending | Approved.
     *
     * @var string
     * @ORM\Column(name="overallKycStatus", type="string")
     */
    private $overallKycStatus = 'No Document Submitted';

    /**
     * MMUser constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // we are guests in BaseMeals
        $this->attendingBaseMeals = new ArrayCollection();
        // we are hosts in meals
        $this->hostingMeals = new ArrayCollection();
        // we are host in tickets
        $this->hostTickets = new ArrayCollection();
        // we are guests in tickets
        $this->guestTickets = new ArrayCollection();

        // we init the hash value ...
        $this->initHash();
    }

    public function __toString()
    {
        return (string) $this->getUsername();
    }

    /**
     * @return mixed
     */
    public function getHostingMeals()
    {
        return $this->hostingMeals;
    }

    /**
     * @param mixed $hostingMeals
     *
     * @return MMUser
     */
    public function setHostingMeals($hostingMeals)
    {
        $this->hostingMeals = $hostingMeals;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHostTickets()
    {
        return $this->hostTickets;
    }

    /**
     * @param mixed $hostTickets
     *
     * @return MMUser
     */
    public function setHostTickets($hostTickets)
    {
        $this->hostTickets = $hostTickets;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGuestTickets()
    {
        return $this->guestTickets;
    }

    /**
     * @param mixed $guestTickets
     *
     * @return MMUser
     */
    public function setGuestTickets($guestTickets)
    {
        $this->guestTickets = $guestTickets;

        return $this;
    }

    /**
     * @return CouponProfile
     */
    public function getCoupontProfile(): ?CouponProfile
    {
        return $this->coupontProfile;
    }

    /**
     * @param CouponProfile $coupontProfile
     *
     * @return MMUser
     */
    public function setCoupontProfile(CouponProfile $coupontProfile): self
    {
        $this->coupontProfile = $coupontProfile;

        return $this;
    }

    /**
     * @return MMUserProfile|null
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param MMUserProfile $profile
     *
     * @return MMUser
     */
    public function setProfile(MMUserProfile $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * @return MMUserSettings|null
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param MMUserSettings $settings
     */
    public function setSettings(MMUserSettings $settings): self
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * @return MMRestaurantProfile|null
     */
    public function getRestaurantProfile()
    {
        return $this->restaurantProfile;
    }

    /**
     * @param mixed $restaurantProfile
     *
     * @return MMUser
     */
    public function setRestaurantProfile($restaurantProfile): self
    {
        $this->restaurantProfile = $restaurantProfile;

        return $this;
    }

    /**
     * @return MMUserPaymentProfile|null
     */
    public function getPaymentProfile(): ?MMUserPaymentProfile
    {
        return $this->paymentProfile;
    }

    /**
     * @param MMUserPaymentProfile $paymentProfile
     *
     * @return MMUser
     */
    public function setPaymentProfile(MMUserPaymentProfile $paymentProfile)
    {
        $this->paymentProfile = $paymentProfile;

        return $this;
    }

    /**
     * Helper to access Mangopay MangopayID. It returns null or the ID.
     *
     * @return string|null
     */
    public function getMangopayID(): ?string
    {
        if ($this->paymentProfile->isComplete()) {
            return $this->paymentProfile->getMangopayID();
        }

        return null;
    }

    /**
     * Helper to access Mangopay WalletID. It returns null or the ID.
     *
     * @return string|null
     */
    public function getMangopayWalletID(): ?string
    {
        if ($this->paymentProfile->isComplete()) {
            return $this->paymentProfile->getMangopayWalletID();
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isTermsAccepted(): bool
    {
        return $this->termsAccepted;
    }

    /**
     * @param bool $termsAccepted
     *
     * @return MMUser
     */
    public function setTermsAccepted(bool $termsAccepted): self
    {
        $this->termsAccepted = $termsAccepted;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOver18(): bool
    {
        return $this->over18;
    }

    /**
     * @param bool $over18
     *
     * @return MMUser
     */
    public function setOver18(bool $over18): self
    {
        $this->over18 = $over18;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFacebookId()
    {
        return $this->facebook_id;
    }

    /**
     * @param mixed $pFacebookId
     *
     * @return MMUser
     */
    public function setFacebookId($pFacebookId)
    {
        $this->facebook_id = $pFacebookId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFacebookAccessToken()
    {
        return $this->facebook_access_token;
    }

    /**
     * @param mixed $pFBAccessToken
     *
     * @return MMUser
     */
    public function setFacebookAccessToken($pFBAccessToken)
    {
        $this->facebook_access_token = $pFBAccessToken;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGoogleId()
    {
        return $this->google_id;
    }

    /**
     * @param mixed $pGoogleId
     *
     * @return MMUser
     */
    public function setGoogleId($pGoogleId)
    {
        $this->google_id = $pGoogleId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGoogleAccessToken()
    {
        return $this->google_access_token;
    }

    /**
     * @param mixed $pGoogleAccessToken
     *
     * @return MMUser
     */
    public function setGoogleAccessToken($pGoogleAccessToken)
    {
        $this->google_access_token = $pGoogleAccessToken;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttendingBaseMeals()
    {
        return $this->attendingBaseMeals;
    }

    /**
     * @param mixed $attendingBaseMeals
     *
     * @return MMUser
     */
    public function setAttendingBaseMeals($attendingBaseMeals)
    {
        $this->attendingBaseMeals = $attendingBaseMeals;

        return $this;
    }

    public function addAttendingBaseMeal(BaseMeal $baseMeal)
    {
        $this->attendingBaseMeals->add($baseMeal);

        return $this;
    }

    /**
     * @param string $overallKycStatus
     *
     * @return MMUser
     */
    public function setOverallKycStatus($overallKycStatus)
    {
        $this->overallKycStatus = $overallKycStatus;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOverallKycStatus()
    {
        return $this->overallKycStatus;
    }
}
