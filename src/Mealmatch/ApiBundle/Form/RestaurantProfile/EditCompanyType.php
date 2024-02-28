<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Form\RestaurantProfile;

use Mealmatch\ApiBundle\ApiConstants;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditCompanyType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //@todo: create a form only for the data required for the payin for a mealticket.
        $builder
            ->add(
                'contactAddress',
                TextType::class,
                array(
                    ApiConstants::LABEL => 'restaurantprofile.contactAddress.label',
                    ApiConstants::TRANSLATION_DOMAIN => ApiConstants::TRANSLATION_DOMAIN_VALUE,
                    ApiConstants::REQUIRED => false,
                    'attr' => array(
                        'class' => 'form-control tbox foo',
                    ),
                )
            )
            ->add(
                'contactPhone',
                TextType::class,
                array(
                    ApiConstants::LABEL => 'restaurantprofile.contactPhone.label',
                    ApiConstants::TRANSLATION_DOMAIN => ApiConstants::TRANSLATION_DOMAIN_VALUE,
                    ApiConstants::REQUIRED => false,
                    'attr' => array(
                        'class' => 'tbox',
                    ),
                )
            )
            ->add(
                'contactEmail',
                EmailType::class,
                array(
                    ApiConstants::LABEL => 'restaurantprofile.contactEmail.label',
                    ApiConstants::TRANSLATION_DOMAIN => ApiConstants::TRANSLATION_DOMAIN_VALUE,
                    ApiConstants::REQUIRED => false,
                    'attr' => array(
                        'class' => 'tbox',
                    ),
                )
            )
            ->add(
                'taxID',
                TextType::class,
                array(
                    ApiConstants::LABEL => 'restaurantprofile.taxID.label',
                    ApiConstants::TRANSLATION_DOMAIN => ApiConstants::TRANSLATION_DOMAIN_VALUE,
                    ApiConstants::REQUIRED => false,
                    'attr' => array(
                        'class' => 'tbox',
                    ),
                )
            )
            ->add(
                'commercialRegisterNumber',
                TextType::class,
                array(
                    ApiConstants::LABEL => 'restaurantprofile.commercialRegisterNumber.label',
                    ApiConstants::TRANSLATION_DOMAIN => ApiConstants::TRANSLATION_DOMAIN_VALUE,
                    ApiConstants::REQUIRED => false,
                    'attr' => array(
                        'class' => 'tbox',
                    ),
                )
            )
            ->add(
                'defaultCurrency',
                TextType::class,
                array(
                    ApiConstants::LABEL => 'restaurantprofile.defaultCurrency.label',
                    ApiConstants::TRANSLATION_DOMAIN => ApiConstants::TRANSLATION_DOMAIN_VALUE,
                    ApiConstants::REQUIRED => false,
                    'attr' => array(
                        'class' => 'tbox',
                    ),
                )
            )
            ->add(
                'taxRate',
                NumberType::class,
                array(
                    ApiConstants::LABEL => 'restaurantprofile.taxRate.label',
                    ApiConstants::TRANSLATION_DOMAIN => ApiConstants::TRANSLATION_DOMAIN_VALUE,
                    ApiConstants::REQUIRED => false,
                    'attr' => array(
                        'class' => 'tbox',
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
        return 'mealmatch_apibundle_restaurantprofile_edit_copmany';
    }
}
