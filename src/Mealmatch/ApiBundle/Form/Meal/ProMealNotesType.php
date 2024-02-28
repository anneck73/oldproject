<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Form\Meal;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProMealNotesType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'mealOfferNotes',
                CKEditorType::class,
                array(
                    'config_name' => 'my_config',
                    'label' => 'promeal.mealoffernotes.label',
                    'translation_domain' => 'Mealmatch',
                    'required' => true,
                ))
            ->add(
                'countryOfferNotes',
                CKEditorType::class,
                array(
                    'config_name' => 'my_config',
                    'label' => 'promeal.countryoffernotes.label',
                    'translation_domain' => 'Mealmatch',
                    'required' => true,
                ))
            ->add(
                'categories',
                EntityType::class,
                array(
                    'class' => 'Mealmatch\ApiBundle\Entity\Meal\BaseMealCategory',
                    'label' => 'meal.category.label',
                    'choice_label' => 'name',

                    'translation_domain' => 'Mealmatch',
                    'required' => true,
                    'multiple' => true,
                    'expanded' => true,
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
            'data_class' => 'Mealmatch\ApiBundle\Entity\Meal\ProMeal',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'mealmatch_apibundle_meal_promeal_notes';
    }
}
