<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMApiBundle\Form;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MealEditType extends AbstractType
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
                    'required' => true,
                )
            )
            ->add(
                'categories',
                EntityType::class,
                array(
                    'class' => 'MMApiBundle:MealCategory',
                    'label' => 'meal.category.label',
                    'choice_label' => 'name',

                    'translation_domain' => 'Mealmatch',
                    'required' => true,
                    'multiple' => true,
                )
            )
            ->add(
                'starter',
                TextType::class,
                array(
                    'label' => 'meal.starter.label',
                    'translation_domain' => 'Mealmatch',
                    'required' => false,
                )
            )
            ->add(
                'main',
                TextType::class,
                array(
                    'label' => 'meal.main.label',
                    'translation_domain' => 'Mealmatch',
                    'required' => true,
                )
            )
            ->add(
                'desert',
                TextType::class,
                array(
                    'label' => 'meal.dessert.label',
                    'translation_domain' => 'Mealmatch',
                    'required' => false,
                )
            )
            ->add(
                'maxNumberOfGuest',
                ChoiceType::class,
                array(
                    'choices' => array(
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                        '5' => '5',
                        '6' => '6',
                        '7' => '7',
                        '8' => '8',
                        '9' => '9',
                        '10' => '10',
                        '11' => '11',
                        '12' => '12',
                        '13' => '13',
                        '14' => '14',
                        '15' => '15',
                    ),
                    'label' => 'meal.maxguest.label',
                    'translation_domain' => 'Mealmatch',
                    'required' => true,
                )
            )
            ->add(
                'sharedCost',
                MoneyType::class,
                array(
                    'label' => 'meal.sharedcost.label',
                    'translation_domain' => 'Mealmatch',
                    'required' => true,
                )
            )
            ->add(
                'sharedCostCurrency',
                HiddenType::class,
                array('data' => 'EUR')
            )
            ->add(
                'locationAddress',
                TextType::class,
                array(
                    'label' => 'meal.locationaddress.label',
                    'translation_domain' => 'Mealmatch',
                    'empty_data' => 'Musterstraße 12, 13089 Berlin',
                    'required' => true,
                )
            )
            ->add(
                'startDateTime',
                DateTimeType::class,
                array(
                    'label' => 'meal.startdatetime.label',
                    'translation_domain' => 'Mealmatch',
                    'required' => true,
                    'input' => 'datetime',
                    'widget' => 'single_text',
                    'with_minutes' => true,
                    'attr' => array('class' => 'form-control input'),
                )
            )
            /*
            ->add('sharedCostCurrency', ChoiceType::class,
                    array(
                        'choices' => array(
                            'EUR' => 'EUR',
                            'CHF' => 'CHF',
                            'USD'  => 'USD'
                        )
                    )
            )*/
            ->add(
                'description',
                CKEditorType::class,
                array(
                    'config_name' => 'my_config',
                    'label' => 'meal.description.label',
                    'translation_domain' => 'Mealmatch',
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MMApiBundle\Entity\Meal',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'mmapibundle_meal';
    }
}
