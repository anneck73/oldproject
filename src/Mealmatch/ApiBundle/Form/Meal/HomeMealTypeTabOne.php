<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Form\Meal;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HomeMealTypeTabOne extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'title',
                TextType::class,
                array(
                    'label' => 'meal.title.label',
                    'translation_domain' => 'Mealmatch',
                )
            )
            ->add(
                'mealMain',
                TextType::class,
                array(
                    'label' => 'meal.main.label',
                    'translation_domain' => 'Mealmatch',
                )
            )
            ->add(
                'mealStarter',
                TextType::class,
                array(
                    'label' => 'meal.starter.label',
                    'translation_domain' => 'Mealmatch',
                    'required' => false,
                )
            )
            ->add(
                'mealDesert',
                TextType::class,
                array(
                    'label' => 'meal.desert.label',
                    'translation_domain' => 'Mealmatch',
                    'required' => false,
                )
            )
            ->add(
                'description',
                CKEditorType::class,
                array(
                    'config_name' => 'my_config',
                    'label' => 'mealoffer.description.label',
                    'translation_domain' => 'Mealmatch',
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
            'data_class' => 'Mealmatch\ApiBundle\Entity\Meal\HomeMeal',
            'currency' => 'EUR',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'mealmatch_apibundle_meal_homemeal_tab_one';
    }
}
