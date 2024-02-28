<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RestaurantPaymentBankAccountType extends AbstractType
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
        return 'mmuserbundle_restaurant_payment_bank_account';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'IBAN',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.iban.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                    'attr' => array('class' => 'tbox'),
                )
            )
            ->add(
                'BIC',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.bic.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                    'attr' => array('class' => 'tbox'),
                )
            )->add(
                'OwnerName',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.paymentowner.name.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                    'attr' => array('class' => 'tbox'),
                )
            )->add(
                'Country',
                CountryType::class,
                array(
                    static::LABEL => 'profile.registration.country',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                    'attr' => array('class' => 'tbox'),
                )
            )->add(
                'PostalCode',
                TextType::class,
                array(
                    static::LABEL => 'profile.registration.areacode',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                    'attr' => array('class' => 'tbox'),
                )
            )->add(
                'Region',
                TextType::class,
                array(
                    static::LABEL => 'profile.registration.state',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                    'attr' => array('class' => 'tbox'),
                )
            )->add(
                'City',
                TextType::class,
                array(
                    static::LABEL => 'profile.registration.city',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                    'attr' => array('class' => 'tbox'),
                )
            )->add(
                'AddressLine1',
                TextType::class,
                array(
                    static::LABEL => 'addressline1',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                    'attr' => array('class' => 'tbox'),
                )
            )->add(
                'AddressLine2',
                TextType::class,
                array(
                    static::LABEL => 'addressline2',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                    'attr' => array('class' => 'tbox'),
                )
            )
        ;
    }
}
