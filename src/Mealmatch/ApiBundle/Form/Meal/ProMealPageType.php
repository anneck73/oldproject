<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Form\Meal;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProMealPageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'tableTopic',
                TextType::class,
                array(
                    'label' => 'promeal.tabletopic.label',
                    'translation_domain' => 'Mealmatch',
                    'required' => true,
                ))
            ->add(
                'maxNumberOfGuest',
                IntegerType::class,
                array(
                    'label' => 'promeal.maxnumberofguests.label',
                    'translation_domain' => 'Mealmatch',
                    'required' => true,
                ))
            ->add(
                'description',
                CKEditorType::class,
                array(
                    'config_name' => 'my_config',
                    'label' => 'mealoffer.description.label',
                    'translation_domain' => 'Mealmatch',
                    'required' => true,
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mealmatch\ApiBundle\Entity\Meal\ProMeal',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'mealmatch_apibundle_meal_promeal_page';
    }
}
