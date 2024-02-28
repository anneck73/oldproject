<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Form\Coupon;

use Mealmatch\ApiBundle\ApiConstants;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CouponWalletAmountType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'amount',
                MoneyType::class,
                array(
                    ApiConstants::LABEL => 'couponwallet.amount',
                    ApiConstants::TRANSLATION_DOMAIN => ApiConstants::TRANSLATION_DOMAIN_VALUE,
                    ApiConstants::REQUIRED => true,
                    'attr' => array(
                        'class' => 'form-control tbox foo',
                    ),
                )
            )
        ;
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
        return 'mealmatch_apibundle_couponwallet_amount';
    }
}
