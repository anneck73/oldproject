<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Form\Meal;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MealEventType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'startDateTime',
                DateTimeType::class,
                array(
                    'label' => 'promeal.startdatetime.label',
                    'translation_domain' => 'Mealmatch',
                    'required' => true,
                    // default anyway ... 'input' => 'datetime',
                    'widget' => 'single_text',
                    // 'with_minutes' => true,
                    'format' => 'dd.MM.y HH:mm',
                    // 'attr' => array('class' => 'form-control input'),
                )
            )
            ->add(
                'endDateTime',
                DateTimeType::class,
                array(
                    'label' => 'promeal.enddatetime.label',
                    'translation_domain' => 'Mealmatch',
                    'required' => false,
                    'widget' => 'single_text',
                    'format' => 'dd.MM.y HH:mm', // <---- HH:mm WTF?! Why where how?
                    // 'with_minutes' => false,
                    // 'attr' => array('class' => 'form-control input'),
                )
            );
        /*
            ->add('allDay')
            ->add('timezone')
            ->add(
                'reoccuring'
            )
            ->add('rrule')
        ;*/
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mealmatch\ApiBundle\Entity\Meal\MealEvent',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'mealmatch_apibundle_meal_mealevent';
    }
}
