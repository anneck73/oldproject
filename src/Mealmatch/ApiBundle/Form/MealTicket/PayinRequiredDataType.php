<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Form\MealTicket;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PayinRequiredDataType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //@todo: create a form only for the data required for the payin for a mealticket.
        $builder
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('address', TextType::class)
            ->add('postalCode', TextType::class)
            ->add('city', TextType::class)
            ->add('region', TextType::class)
            ->add('birthday', BirthdayType::class, array('widget' => 'single_text'))
            ->add('nationality', CountryType::class, array(
                'placeholder' => 'Please choose',
            ))
            ->add('countryOfResidence', CountryType::class, array(
                'placeholder' => 'Please choose',
            ))
        ;
//        $builder->add('paymentType', ChoiceType::class, array(
//            'choices' => array(
//                'mealticket.paymentoption.card' => 'CARD',
//                'mealticket.paymentoption.giropay' => 'GIROPAY',
//                'mealticket.paymentoption.sofort' => 'SOFORT',
//            ),
//            'label' => 'mealticket.payment.options',
//            'choice_translation_domain' => 'Mealmatch',
//            'translation_domain' => 'Mealmatch',
//            'attr' => array('onChange' => 'this.form.submit()'),
//        ));
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
        return 'mealmatch_apibundle_meal_basemealticket_payinrequireddata';
    }
}
