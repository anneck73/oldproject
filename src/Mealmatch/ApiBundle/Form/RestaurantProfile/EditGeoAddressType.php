<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Form\RestaurantProfile;

use Mealmatch\ApiBundle\ApiConstants;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class EditGeoAddressType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'locationString',
                TextType::class,
                array(
                    ApiConstants::LABEL => 'restaurantprofile.locationString.label',
                    ApiConstants::TRANSLATION_DOMAIN => ApiConstants::TRANSLATION_DOMAIN_VALUE,
                    ApiConstants::REQUIRED => true,
                    'attr' => array(
                        'class' => 'form-control tbox foo',
                    ),
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('min' => 12)
                        ),
                    ),
                )
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        // $resolver->setDefaults(array());
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'mealmatch_apibundle_restaurantprofile_edit_geo_address';
    }
}
