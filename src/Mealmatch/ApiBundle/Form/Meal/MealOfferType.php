<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Form\Meal;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MealOfferType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add(
                'description',
                CKEditorType::class,
                array(
                    'config_name' => 'my_config',
                    'label' => 'mealoffer.description.label',
                    'translation_domain' => 'Mealmatch',
                )
            )
            ->add(
                'price',
                MoneyType::class,
                array(
                    'label' => 'mealoffer.price.label',
                    'translation_domain' => 'Mealmatch',
                    'required' => true,
                    'currency' => 'EUR',
                )
            )
            // ->add('currency')
            // ->add('availableAmount')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mealmatch\ApiBundle\Entity\Meal\MealOffer',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'mealmatch_apibundle_meal_mealoffer';
    }
}
