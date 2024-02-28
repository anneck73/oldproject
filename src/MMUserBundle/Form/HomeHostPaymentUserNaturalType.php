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

class HomeHostPaymentUserNaturalType extends AbstractType
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
        return 'mmuserbundle_homehost_payment_user_natural';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'FirstName',
                TextType::class,
                array(
                    static::LABEL => 'firstName',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )->add(
                'LastName',
                TextType::class,
                array(
                    static::LABEL => 'lastName',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'AddressLine1',
                TextType::class,
                array(
                    static::LABEL => 'addressline1',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'AddressLine2',
                TextType::class,
                array(
                    static::LABEL => 'addressline2',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'City',
                TextType::class,
                array(
                    static::LABEL => 'profile.registration.city',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'Region',
                TextType::class,
                array(
                    static::LABEL => 'profile.registration.state',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'PostalCode',
                TextType::class,
                array(
                    static::LABEL => 'profile.registration.areacode',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'Country',
                CountryType::class,
                array(
                    static::LABEL => 'profile.registration.country',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )->add(
                'Birthday',
                BirthdayType::class,
                array(
                    static::LABEL => 'profile.registration.birthday',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                    'attr' => array('class' => 'tbox date-pkr'),
                )
            )->add(
                'CountryOfResidence',
                CountryType::class,
                array(
                    static::LABEL => 'profile.registration.countryOfResidence',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )->add(
                'Nationality',
                CountryType::class,
                array(
                    static::LABEL => 'profile.registration.nationality',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )->add(
                'Email',
                EmailType::class,
                array(
                    static::LABEL => 'email.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
        ;
    }
}
