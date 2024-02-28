<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RestaurantPaymentUserLegalType extends AbstractType
{
    const LABEL = 'label';
    const TRANSLATION_DOMAIN = 'translation_domain';
    const REQUIRED = 'required';
    const BUNDLE_NAME = 'Mealmatch';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'mmuserbundle_restaurant_payment_user_legal';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'Name',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.company.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                    'attr' => array('class' => 'tbox'),
                )
            )
            ->add(
                'CompanyNumber',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.taxidShort.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                    'attr' => array('class' => 'tbox'),
                )
            )
            ->add(
                'Email',
                EmailType::class,
                array(
                    static::LABEL => 'restaurantprofile.paymentemail.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                    'attr' => array('class' => 'tbox'),
                )
            )
            ->add(
                'HQAddressLine1',
                TextType::class,
                array(
                    static::LABEL => 'payment.userLegal.hq.addressline1.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'HQAddressLine2',
                TextType::class,
                array(
                    static::LABEL => 'payment.userLegal.hq.addressline2.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'HQCity',
                TextType::class,
                array(
                    static::LABEL => 'profile.registration.city',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'HQRegion',
                TextType::class,
                array(
                    static::LABEL => 'profile.registration.state',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'HQPostalCode',
                TextType::class,
                array(
                    static::LABEL => 'profile.registration.areacode',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'HQCountry',
                CountryType::class,
                array(
                    static::LABEL => 'profile.registration.country',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'LRAddressLine1',
                TextType::class,
                array(
                    static::LABEL => 'payment.legalrepresentative.addressline1',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'LRAddressLine2',
                TextType::class,
                array(
                    static::LABEL => 'payment.legalrepresentative.addressline2',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'LRAddressCity',
                TextType::class,
                array(
                    static::LABEL => 'profile.registration.city',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'LRAddressRegion',
                TextType::class,
                array(
                    static::LABEL => 'profile.registration.state',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )->add(
                'LRAddressPostalCode',
                TextType::class,
                array(
                    static::LABEL => 'profile.registration.areacode',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )->add(
                'LRAddressCountry',
                CountryType::class,
                array(
                    static::LABEL => 'profile.registration.country',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )->add(
                'LRBirthday',
                BirthdayType::class,
                array(
                    static::LABEL => 'profile.registration.birthday',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                    'attr' => array('class' => 'tbox date-pkr'),
                )
            )->add(
                'LRCountryOfResidence',
                CountryType::class,
                array(
                    static::LABEL => 'profile.registration.countryOfResidence',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )->add(
                'LRNationality',
                CountryType::class,
                array(
                    static::LABEL => 'profile.registration.nationality',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )->add(
                'LREmail',
                EmailType::class,
                array(
                    static::LABEL => 'email.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )->add(
                'LRFirstName',
                TextType::class,
                array(
                    static::LABEL => 'payment.legalrepresentative.firstName',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )->add(
                'LRLastName',
                TextType::class,
                array(
                    static::LABEL => 'payment.legalrepresentative.lastName',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
        ;
    }
}
