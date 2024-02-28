<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Form\RestaurantProfile;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Mealmatch\ApiBundle\ApiConstants;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditDescriptionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //@todo: create a form only for the data required for the payin for a mealticket.
        $builder
            ->add(
                'company',
                TextType::class,
                array(
                    ApiConstants::LABEL => 'restaurantprofile.company.label',
                    ApiConstants::TRANSLATION_DOMAIN => ApiConstants::TRANSLATION_DOMAIN_VALUE,
                    ApiConstants::REQUIRED => false,
                    'attr' => array(
                        'class' => 'form-control tbox foo',
                    ),
                )
            )
            ->add(
                'name',
                TextType::class,
                array(
                    ApiConstants::LABEL => 'restaurantprofile.name.label',
                    ApiConstants::TRANSLATION_DOMAIN => ApiConstants::TRANSLATION_DOMAIN_VALUE,
                    ApiConstants::REQUIRED => false,
                    'attr' => array(
                        'class' => 'tbox',
                    ),
                )
            )
            ->add(
                'type',
                TextType::class,
                array(
                    ApiConstants::LABEL => 'restaurantprofile.type.label',
                    ApiConstants::TRANSLATION_DOMAIN => ApiConstants::TRANSLATION_DOMAIN_VALUE,
                    ApiConstants::REQUIRED => false,
                    'attr' => array(
                        'class' => 'tbox',
                    ),
                )
            )
            ->add(
                'description',
                CKEditorType::class,
                array(
                    ApiConstants::LABEL => 'restaurantprofile.type.label',
                    ApiConstants::TRANSLATION_DOMAIN => ApiConstants::TRANSLATION_DOMAIN_VALUE,
                    ApiConstants::REQUIRED => false,
                    'attr' => array(
                        'class' => 'tbox',
                    ),
                    'config_name' => 'my_config',
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
        return 'mealmatch_apibundle_restaurantprofile_edit_desc';
    }
}
