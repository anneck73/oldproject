<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle;

final class ApiConstants
{
    public const VERSION = '0.2.21';
    /** @var float DEFAULT_TAX_RATE the default tax rate in germany 19%. */
    public const DEFAULT_TAX_RATE = 0.19;
    /** @var string DEFAULT_CURRENCY the default currency in germany EUR */
    public const DEFAULT_CURRENCY = 'EUR';
    /**
     * MealTypes:
     *      BaseMeal (base clase used for common functions)
     *      HomeMeal
     *      ProMeal
     * They MUST BE EQUAL TO THE SHORTNAME OF THE ENITITY CLASS!
     */
    public const MEAL_TYPE_PRO = 'ProMeal';
    public const ENTITY_PRO_MEAL = 'ApiBundle:Meal\ProMeal';
    public const MEAL_TYPE_HOME = 'HomeMeal';
    public const ENTITY_HOME_MEAL = 'ApiBundle:Meal\HomeMeal';
    /**
     * Used by Enterprise JSON Data models.
     */
    public const JSON_PROCESSOR_CLASS_SUFFIX = 'JSON_Processor';
    /**
     * Meal Status Values for all types of meals!
     */
    /**
     * CREATED meals have been created in the DB.
     */
    public const MEAL_STATUS_CREATED = 'CREATED';
    /**
     * READY meals are just waiting to get RUNNING.
     */
    public const MEAL_STATUS_READY = 'READY';
    /**
     * RUNNING meals are considered 'active'.
     */
    public const MEAL_STATUS_RUNNING = 'RUNNING';
    /**
     * STOPPED meals have been stopped for some reason.
     */
    public const MEAL_STATUS_STOPPED = 'STOPPED';
    /**
     * FINISHED meals ended successfully.
     */
    public const MEAL_STATUS_FINISHED = 'FINISHED';
    /**
     * DELETED meals have been deleted for some reason.
     */
    public const MEAL_STATUS_DELETED = 'DELETED';

    /**
     * MealTickets.
     */
    public const MEAL_TICKET_STATUS_CREATED = 'CREATED';
    public const MEAL_TICKET_STATUS_PROCESSING = 'PROCESSING_PAYMENT';
    public const MEAL_TICKET_STATUS_ERROR = 'PAYMENT_ERROR';
    public const MEAL_TICKET_STATUS_PAYED = 'PAYED';
    public const MEAL_TICKET_STATUS_CANCELLED = 'CANCELLED';
    public const MEAL_TICKET_STATUS_USED = 'USED';

    public const MEAL_TICKET_PAYIN_STATUS_NOT_CREATED = 'NOT_CREATED';

    /**
     * Restaurant legal file types.
     */
    public const LEGAL_FILE_TYPE_BUSINESS_REGISTRATION = 'BUSINESS_REGISTRATION';
    public const LEGAL_FILE_TYPE_OTHER = 'OTHER';

    /**
     * JoinRequest.
     */
    public const JOIN_REQ_STATUS_CREATED = 'CREATED';
    public const JOIN_REQ_STATUS_ACCEPTED = 'ACCEPTED';
    public const JOIN_REQ_STATUS_DENIED = 'DENIED';
    public const JOIN_REQ_STATUS_PAYED = 'PAYED';
    public const JOIN_REQ_STATUS_PAYMENT_FAILED = 'PAYMENT_FAILED';

    /**
     * General.
     */
    public const EMPTY_STRING = '---';
    public const BUNDLE_NAME = 'ApiBundle';
    public const TRANSLATION_DOMAIN = 'translation_domain';
    public const TRANSLATION_DOMAIN_VALUE = 'Mealmatch';
    public const REQUIRED = 'required';
    public const LABEL = 'label';

    /**
     * MealTicketTransaction Transaction Type.
     */
    public const TRANSACTION_TYPE_PAYIN = 'PAYIN';
    public const TRANSACTION_TYPE_PAYOUT = 'PAYOUT';
    public const TRANSACTION_TYPE_TRANSFER = 'TRANSFER';
    // ??
    public const TRANSACTION_TYPE_PAYOUT_FAILED = 'PAYOUT_FAILED';
    /**
     * MealTicketTransaction Payment Status.
     */
    public const TRANSACTION_STATUS_CREATED = 'CREATED';
    public const TRANSACTION_STATUS_SUCCEEDED = 'SUCCEEDED';
    public const TRANSACTION_STATUS_FAILED = 'FAILED';
    public const PAYOUT_STATUS_FAILED = 'FAILED';

    /**
     * kyc.
     */
    public const KYC_CREATED = 'CREATED';
    public const VALIDATION_ASKED = 'VALIDATION_ASKED';
    public const KYC_SUCCEEDED = 'VALIDATED';
    public const KYC_FAILED = 'REFUSED';

    /**
     * Coupons.
     */
    public const COUPON_TYPE_MEAL = 'COUPON_TYPE_MEAL';
    public const COUPON_TYPE_DEFAULT = 'COUPON_TYPE_DEFAULT';

    /**
     * Redeem requests status values.
     */
    public const REDEEM_REQ_STATUS_NEW = 'REDEEM_REQ_NEW';
    public const REDEEM_REQ_STATUS_GRANTED = 'REDEEM_REQ_GRANTED';
    public const REDEEM_REQ_STATUS_DENIED = 'REDEEM_REQ_DENIED';
    public const REDEEM_REQ_STATUS_ERROR = 'REDEEM_REQ_ERROR';

    /**
     * CouponData valid status values.
     */
    public const COUPON_NEW = 'COUPON_NEW';
    public const COUPON_ACTIVE = 'COUPON_ACTIVE';
    public const COUPON_STOPPED = 'COUPON_STOPPED';

    /**
     * CouponWallet valid status values.
     */
    public const COUPON_WALLET_VALIDATION_ASKED = 'WALLET_IS_NEW';
    public const COUPON_WALLET_VALIDATED = 'WALLET_IS_VALIDATED';
    public const COUPON_WALLET_REFUSED = 'WALLET_IS_REFUSED';
    public const COUPON_WALLET_CLOSED = 'WALLET_IS_CLOSED';
    public const COUPON_WALLET_EMPTY = 'WALLET_IS_EMPTY';

    /**
     * ViewData Trait and TWIG templates use this string key.
     */
    public const CURRENT_ROUTE = 'currentRoute';

    /**
     * Mealmatch specific Role constant values.
     */
    public const ROLE_RESTAURANT_USER = 'ROLE_RESTAURANT_USER';
    public const ROLE_HOME_USER = 'ROLE_HOME_USER';

    /**
     * Mealmatch enterprise community group pre/suffix.
     */
    // Enterprise groups associated to the default user registration (HomeUser-Role)
    public const MEALMATCH_DEFAULT_GROUP = 'MM_GROUP_DEFAULT';
    // Dynamic community groups share a suffix
    public const MEALMATCH_COMMUNITY_GROUP_SUFFIX = '_C_GROUP';
    // A group with this attribute in its properties
    public const MEALMATCH_COMMUNITY_GROUP_ATTRIB = 'C_GROUP_ATTRIB';
    public const MEALMATCH_C_GROUP_SUFFIX = self::MEALMATCH_COMMUNITY_GROUP_SUFFIX;

    /**
     * Community Voter Constants.
     */
    // Community attributes / available actions with subject to check for permission.
    // create, view, edit, administration
    public const COMMUNITY_CREATE = 'COMMUNITY_CREATE';
    public const COMMUNITY_VIEW = 'COMMUNITY_VIEW';
    public const COMMUNITY_EDIT = 'COMMUNITY_EDIT';
    public const COMMUNITY_ADMINISTRATION = 'COMMUNITY_ADMINISTRATION';

    // Community groups: create, view, edit, administration
    public const COMMUNITY_CREATE_GROUP = 'COMMUNITY_CREATE_GROUP';
    public const COMMUNITY_VIEW_GROUP = 'COMMUNITY_VIEW_GROUP';
    public const COMMUNITY_EDIT_GROUP = 'COMMUNITY_EDIT_GROUP';
    public const COMMUNITY_GROUP_ADMINISTRATION = 'COMMUNITY_GROUP_ADMINISTRATION';

    /**
     * Community roles.
     */
    public const ROLE_COMMUNITY_ADMIN = 'ROLE_COMMUNITY_ADMIN';
    public const ROLE_COMMUNITY_GROUP_ADMIN = 'ROLE_COMMUNITY_GROUP_ADMIN';
    public const ROLE_COMMUNITY_MEMBER = 'ROLE_COMMUNITY_MEMBER';

    /**
     * NEW Mealmatch Voter Permissions.
     */
    // The owner (user) is allowed to do anything
    public const HOME_OWNER = 'HOME_OWNER';
    // Anyone in the Admin-Group
    public const HOME_MEAL_ADMIN = 'HOME_MEAL_ADMIN';

    public const RESTAURANT_OWNER = 'RESTAURANT_OWNER';
    public const PRO_MEAL_ADMIN = 'PRO_MEAL_ADMIN';
}
