<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Form;

use Mealmatch\ApiBundle\ApiConstants;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class RestaurantFileType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'fileData',
                VichImageType::class,
                array(
                    'required' => true,
                    'allow_delete' => false,
                    'download_label' => 'download',
                    'download_uri' => false,
                    'image_uri' => false,
                    'label' => 'restaurantprofile.legalfile.label',
                    'translation_domain' => 'Mealmatch',
                )
            )
            ->add(
                'legalType',
                ChoiceType::class,
                array(
                    'choices' => array(
                        'restaurantprofile.business.data.dropdown.businessregistration' => ApiConstants::LEGAL_FILE_TYPE_BUSINESS_REGISTRATION,
                        'restaurantprofile.business.data.dropdown.other' => ApiConstants::LEGAL_FILE_TYPE_OTHER,
                    ),
                    'required' => true,
                    'label' => 'restaurantprofile.legalfiletype.label',
                    'translation_domain' => 'Mealmatch',
                    'data' => ApiConstants::LEGAL_FILE_TYPE_BUSINESS_REGISTRATION,
                )
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MMUserBundle\Entity\RestaurantFile',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'mealmatch_mmuserbundle_restaurant_file';
    }
}
