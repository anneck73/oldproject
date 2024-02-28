<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Form\Meal;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HomeMealTypeTabTwo extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
                    'expanded' => false,
                )
            )
            ->add(
                'maxNumberOfGuest',
                IntegerType::class,
                array(
                    'label' => 'meal.maxguest.label',
                    'translation_domain' => 'Mealmatch',
                    'required' => true,
                    'attr' => array(
                        'min' => 1,
                        'max' => 15,
                    ),
                )
            )
            ->add(
                'sharedCost',
                MoneyType::class,
                array(
                    'currency' => $options['currency'],
                    'scale' => 2,
                    'grouping' => true,
                    'label' => 'meal.sharedcost.label',
                    'translation_domain' => 'Mealmatch',
                    'required' => true,
                ))
            ->add(
                'sharedCostCurrency',
                CurrencyType::class,
                array(
                    'choices' => array(
                        'EUR' => 'EUR',
                        'USD' => 'USD',
                    ),
                    'label' => 'meal.currency.label',
                    'translation_domain' => 'Mealmatch',
                    'required' => true,
                )
            )
            ->add(
                'countryCategory',
                CountryType::class,
                array(
                    'label' => 'meal.countryCategory.label',
                    'translation_domain' => 'Mealmatch',
                )
            );
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
        return 'mealmatch_apibundle_meal_homemeal_tab_two';
    }
}
