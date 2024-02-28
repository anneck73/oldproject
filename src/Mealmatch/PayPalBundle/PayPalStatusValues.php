<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\PayPalBundle;

/**
 * PayPal status value constant's.
 */
final class PayPalStatusValues
{
    /**
     * Completed: The payment has been completed, and the funds have been added successfully
     *      to your account balance.
     */
    const IPN_STATUS_COMPLETED = 'COMPLETED';
    /**
     * Canceled_Reversal: A reversal has been canceled. For example, you won a dispute with the customer,
     *      and the funds for the transaction that was reversed have been returned to you.
     */
    const IPN_STATUS_CANCELED_REVERSAL = 'CANCELED_REVERSAL';
    /**
     * Created: A German ELV payment is made using Express Checkout.
     */
    const IPN_STATUS_CREATED = 'CREATED';
    /**
     * Denied: You denied the payment. This happens only if the payment was previously pending because of
     *      possible reasons described for the pending_reason variable or the Fraud_Management_Filters_x variable.
     */
    const IPN_STATUS_DENIED = 'DENIED';
    /**
     * Expired: This authorization has expired and cannot be captured.
     */
    const IPN_STATUS_EXPIRED = 'EXPIRED';
    /**
     * Failed: The payment has failed. This happens only if the payment was made from your customer’s bank account.
     */
    const IPN_STATUS_FAILED = 'FAILED';
    /**
     * Pending: The payment is pending. See pending_reason for more information.
     */
    const IPN_STATUS_PENDING = 'PENDING';
    /**
     * Refunded: You refunded the payment.
     */
    const IPN_STATUS_REFUNDED = 'REFUNDED';
    /**
     * Reversed: A payment was reversed due to a chargeback or other type of reversal. The funds have been removed
     *      from your account balance and returned to the buyer. The reason for the reversal is specified in the
     *      ReasonCode element.
     */
    const IPN_STATUS_REVERSED = 'REVERSED';
    /**
     * Processed: A payment has been accepted.
     */
    const IPN_STATUS_PROCESSED = 'PROCESSED';
    /**
     * Voided: This authorization has been voided.
     */
    const IPN_STATUS_VOIDED = 'VOIDED';
}
