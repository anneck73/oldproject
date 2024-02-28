<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\CouponBundle\Form;

use Mealmatch\ApiBundle\SymfonyConstants;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RedeemRequestType extends AbstractType
{
    const BUNDLE_NAME = 'CouponBundle';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'codeString',
                TextType::class,
                array(
                    SymfonyConstants::REQUIRED => true,
                    SymfonyConstants::LABEL => 'coupon.couponcode',
                    SymfonyConstants::TRANSLATION_DOMAIN => 'Mealmatch',
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mealmatch\ApiBundle\Entity\Coupon\RedeemRequest',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'mealmatch_apibundle_coupon_redemm_request';
    }
}
