<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Form;

use Mealmatch\ApiBundle\ApiConstants;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RestaurantProfileSettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MMUserBundle\Entity\MMRestaurantProfile',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'mmuserbundle_mmrestaurantprofile_settings';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'taxRate',
                NumberType::class,
                array(
                    ApiConstants::LABEL => 'restaurantprofile.taxrate.label',
                    ApiConstants::TRANSLATION_DOMAIN => ApiConstants::BUNDLE_NAME,
                    ApiConstants::REQUIRED => false,
                )
            )
            ->add(
                'defaultCurrency',
                TextType::class,
                array(
                    ApiConstants::LABEL => 'restaurantprofile.default-currency.label',
                    ApiConstants::TRANSLATION_DOMAIN => ApiConstants::BUNDLE_NAME,
                    ApiConstants::REQUIRED => false,
                )
            );
    }
}
