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
 * The class MMUserPaymentProfile collects all user specific payment data required for the mealmatch payment process.
 *
 * @ORM\Table(name="mm_user_payment_profile")
 * @ORM\Entity()
 */
class MMUserPaymentProfile extends AbstractEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="payPalEmail", type="string", length=255, nullable=true)
     */
    private $payPalEmail;

    /**
     * @var string|null
     *
     * @ORM\Column(name="mangopayID", type="string", length=64, nullable=true)
     */
    private $mangopayID;

    /**
     * @var string|null;
     *
     * @ORM\Column(name="mangopayWalletID", type="string", length=64, nullable=true)
     */
    private $mangopayWalletID;

    /**
     * @var integer|null;
     *
     * @ORM\Column(name="mangopayBankAccountId", type="integer", nullable=true)
     */
    private $mangopayBankAccountId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="paymentMethod", type="string", length=55, nullable=true)
     */
    private $paymentMethod;

    /**
     * @var string|null
     *
     * @ORM\Column(name="iban", type="string", nullable=true)
     */
    private $iban;

    /**
     * @var string|null
     *
     * @ORM\Column(name="bic", type="string", nullable=true)
     */
    private $bic;

    /**
     * The payment profile is valid only if all payment related features are working for this user.
     *
     * @var bool
     */
    private $valid = false;

    public function __toString()
    {
        return __CLASS__.$this->getId();
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * @param bool $valid
     *
     * @return MMUserPaymentProfile
     */
    public function setValidity(bool $valid): self
    {
        $this->valid = $valid;

        return $this;
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
     * @return MMUserPaymentProfile
     */
    public function setPayPalEmail($payPalEmail)
    {
        $this->payPalEmail = $payPalEmail;

        return $this;
    }

    /**
     * @return bool true if a MangopayID is not null, else false;
     */
    public function hasMangopayID(): bool
    {
        if (null === $this->mangopayID) {
            return false;
        }

        return true;
    }

    /**
     * @return bool true if a MangopayWalletID is not null, else false;
     */
    public function hasMangopayWalletID(): bool
    {
        if (null === $this->mangopayWalletID) {
            return false;
        }

        return true;
    }

    /**
     * @return string|null
     */
    public function getMangopayID(): ?string
    {
        return $this->mangopayID;
    }

    /**
     * @param string|null $mangopayID
     *
     * @return MMUserPaymentProfile
     */
    public function setMangopayID(?string $mangopayID): self
    {
        $this->mangopayID = $mangopayID;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMangopayWalletID(): ?string
    {
        return $this->mangopayWalletID;
    }

    /**
     * @param string|null $mangopayWalletID
     *
     * @return MMUserPaymentProfile
     */
    public function setMangopayWalletID(?string $mangopayWalletID): self
    {
        $this->mangopayWalletID = $mangopayWalletID;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMangopayBankAccountId(): ?int
    {
        return $this->mangopayBankAccountId;
    }

    /**
     * @param int|null $mangopayBankAccountId
     *
     * @return MMUserPaymentProfile
     */
    public function setMangopayBankAccountId(?int $mangopayBankAccountId): self
    {
        $this->mangopayBankAccountId = $mangopayBankAccountId;

        return $this;
    }

    /**
     * Check is mangopayBankAccountID exists.
     *
     * @return bool
     */
    public function hasMangopayBankAccountId(): bool
    {
        if (null === $this->mangopayBankAccountId) {
            return false;
        }

        return true;
    }

    /**
     * @return string|null
     */
    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    /**
     * @TODO: FIXME!
     *
     * ATM this is used to store the "directDebitType" values: "SOFORT", "GIROPAY";
     *
     * @see https://docs.mangopay.com/endpoints/v2.01/payins#e281_create-a-direct-debit-web-payin
     *
     * @param string|null $paymentMethod
     *
     * @return MMUserPaymentProfile
     */
    public function setPaymentMethod(?string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIban(): ?string
    {
        return $this->iban;
    }

    /**
     * @param string|null $iban
     *
     * @return MMUserPaymentProfile
     */
    public function setIban(?string $iban): self
    {
        $this->iban = $iban;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBic(): ?string
    {
        return $this->bic;
    }

    /**
     * @param string|null $bic
     *
     * @return MMUserPaymentProfile
     */
    public function setBic(?string $bic): self
    {
        $this->bic = $bic;

        return $this;
    }

    /**
     * Returns true if the UserPaymentProfile is considered complete.
     *
     * @return bool
     */
    public function isComplete(): bool
    {
        if ($this->hasMangopayID() && $this->hasMangopayWalletID()) {
            return true;
        }

        return false;
    }
}
