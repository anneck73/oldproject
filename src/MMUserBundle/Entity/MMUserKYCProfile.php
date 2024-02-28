<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mealmatch\ApiBundle\Entity\AbstractEntity;

/**
 * The class MMUserKYCProfile collects all user KYC specific data.
 *
 * @ORM\Table(name="mm_user_kyc_profile")
 * @ORM\Entity(repositoryClass="MMUserBundle\Repository\MMUserKYCProfileRepository")
 */
class MMUserKYCProfile extends AbstractEntity
{
    /**
     * @var int|null
     *
     * @ORM\Column(name="mangopayUserID", type="integer", nullable=true)
     */
    private $mangopayUserID;

    /**
     * @var int|null
     *
     * @ORM\Column(name="userID", type="integer", nullable=true)
     */
    private $userID;

    /**
     * @var integer|null;
     *
     * @ORM\Column(name="kyc_id", type="integer", nullable=true)
     */
    private $kycId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="kycDocType", type="string", nullable=true)
     */
    private $kycDocType;

    /**
     * @var string|null
     *
     * @ORM\Column(name="status", type="string", nullable=true)
     */
    private $status;

    /**
     * @var array|null
     */
    private $kycDocCode;

    /**
     * @var string|null
     *
     * @ORM\Column(name="kycDocSubmitted", type="string", nullable=true)
     */
    private $kycDocSubmitted;

    /**
     * Get mangopayUserID.
     *
     * @return int
     */
    public function getMangopayUserID()
    {
        return $this->mangopayUserID;
    }

    /**
     * @param mixed $mangopayUserID
     *
     * @return MMUserKYCProfile
     */
    public function setMangopayUserID($mangopayUserID)
    {
        $this->mangopayUserID = $mangopayUserID;

        return $this;
    }

    /**
     * Get UserID.
     *
     * @return int
     */
    public function getUserID()
    {
        return $this->userID;
    }

    /**
     * @param mixed $userID
     *
     * @return MMUserKYCProfile
     */
    public function setUserID($userID)
    {
        $this->userID = $userID;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getKycId(): ?int
    {
        return $this->kycId;
    }

    /**
     * @param int|null $mangopayID
     *
     * @return MMUserKYCProfile
     */
    public function setKycId(?int $kycId): self
    {
        $this->kycId = $kycId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getKycDocType(): ?string
    {
        return $this->kycDocType;
    }

    /**
     * @param string|null $kycDocType
     *
     * @return MMUserKYCProfile
     */
    public function setKycDocType(?string $kycDocType): self
    {
        $this->kycDocType = $kycDocType;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     *
     * @return MMUserKYCProfile
     */
    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getKycDocCode(): ?array
    {
        return $this->kycDocCode;
    }

    /**
     * @param array|null $kycDocCode
     *
     * @return MMUserKYCProfile
     */
    public function setKycDocCode(?array $kycDocCode): self
    {
        $this->kycDocCode = $kycDocCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getKycDocSubmitted(): ?string
    {
        return $this->kycDocSubmitted;
    }

    /**
     * @param string|null $kycDocSubmitted
     *
     * @return MMUserKYCProfile
     */
    public function setKycDocSubmitted(?string $kycDocSubmitted): self
    {
        $this->kycDocSubmitted = $kycDocSubmitted;

        return $this;
    }
}
