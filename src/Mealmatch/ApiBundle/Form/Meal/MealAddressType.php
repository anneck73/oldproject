<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Form\Meal;

use Mealmatch\ApiBundle\Entity\Meal\MealAddress;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MealAddressType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'locationString',
                TextType::class,
                array(
                    'label' => 'mealaddress.locationstring.label',
                    'translation_domain' => 'Mealmatch',
                )
            )
            ;
        /*
        ->add('country')
        ->add('countryCode')
        ->add('state')
        ->add('city')
        ->add('postalCode')
        ->add('streetName')
        ->add('streetNumber')
        ->add('extraLine1')
        ->add('extraLine2')
        ->add('locality')
        ->add('sublocality')
        ->add('description')
        ->add('bounds')
        ->add('hash');*/

        // Form Events...
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var MealAddress $mealAddr */
            $mealAddr = $event->getData();
            $mealForm = $event->getForm();
            if (null !== $mealAddr && '-' !== $mealAddr->getLocationString()) {
                $mealForm->add(
                    'bellSign',
                    TextType::class,
                   array(
                       'label' => 'mealaddress.bellsign.label',
                       'translation_domain' => 'Mealmatch',
                       'required' => false,
                   )
               );
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mealmatch\ApiBundle\Entity\Meal\MealAddress',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'mealmatch_apibundle_meal_mealaddress';
    }
}
