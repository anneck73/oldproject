<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MMRestaurantProfileType extends AbstractType
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
        return 'mmuserbundle_mmrestaurantprofile';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.registration.name',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                ))
            ->add(
                'company',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.registration.company',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                ))
            ->add(
                'type',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.registration.type',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                ))
            ->add(
                'pictures',
                CollectionType::class, array(
                'entry_type' => RestaurantImageType::class,
                'entry_options' => array('label' => false),
                'allow_add' => true,
                'by_reference' => false,
            ));
    }
}
