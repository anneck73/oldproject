<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Form\MealTicket;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentOptionsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('paymentType', ChoiceType::class, array(
            'choices' => array(
                'mealticket.paymentoption.card' => 'CARD',
                'mealticket.paymentoption.giropay' => 'GIROPAY',
                'mealticket.paymentoption.sofort' => 'SOFORT',
            ),
            'label' => 'mealticket.payment.options',
            'choice_translation_domain' => 'Mealmatch',
            'translation_domain' => 'Mealmatch',
            'attr' => array('onChange' => 'this.form.submit()'),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'mealmatch_apibundle_meal_basemealticket_paymentoptions';
    }
}
