<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RestaurantProfileBusinessType extends AbstractType
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
            'data_class' => 'MMUserBundle\Entity\MMRestaurantProfile',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'mmuserbundle_mmrestaurantprofile_business';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'commercialRegisterNumber',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.regnumber.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'taxID',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.taxid.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'bankIBAN',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.iban.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'bankBIC',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.bic.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'payPalEmail',
                EmailType::class,
                array(
                    static::LABEL => 'restaurantprofile.paypalemail.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'authorizedRepresentative',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.authorized.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'contactAddress',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.contactaddress.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'contactEmail',
                EmailType::class,
                array(
                    static::LABEL => 'restaurantprofile.contactemail.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )->add(
                'contactPhone',
                TelType::class,
                array(
                    static::LABEL => 'restaurantprofile.contactphone.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
        ;
    }
}
