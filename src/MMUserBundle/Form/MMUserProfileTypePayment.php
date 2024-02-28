<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Form;

use Mealmatch\ApiBundle\ApiConstants;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * UserProfile PaymentFormType.
 */
class MMUserProfileTypePayment extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'paymentMethod',
                ChoiceType::class,
                array(
                    ApiConstants::LABEL => 'Payment Method',
                    ApiConstants::TRANSLATION_DOMAIN => ApiConstants::BUNDLE_NAME,
                    ApiConstants::REQUIRED => true,
                    'choices' => array(
                        'Kreditkarte' => 'CARD',
                        'Giropay' => 'GIROPAY',
                        'Sofort' => 'SOFORT',
                    ),
                )
            )
//            ->add(
//                'payPalEmail',
//                EmailType::class,
//                array(
//                    ApiConstants::LABEL => 'profile.registration.paypalemail',
//                    ApiConstants::TRANSLATION_DOMAIN => ApiConstants::BUNDLE_NAME,
//                    ApiConstants::REQUIRED => false,
//                )
//            )
            ->add('mangopayID',
                TextType::class,
                array(
                    ApiConstants::LABEL => 'mangopayID',
                    ApiConstants::TRANSLATION_DOMAIN => ApiConstants::BUNDLE_NAME,
                    ApiConstants::REQUIRED => false,
                    'disabled' => true,
                ))
            ->add('mangopayWalletID',
                TextType::class,
                array(
                    ApiConstants::LABEL => 'mangopayWalletID',
                    ApiConstants::TRANSLATION_DOMAIN => ApiConstants::BUNDLE_NAME,
                    ApiConstants::REQUIRED => false,
                    'disabled' => true,
                ))
            ->add('mangopayBankAccountID',
            TextType::class,
                array(
                    ApiConstants::LABEL => 'mangopayBankAccountID',
                    ApiConstants::TRANSLATION_DOMAIN => ApiConstants::BUNDLE_NAME,
                    ApiConstants::REQUIRED => false,
                    'disabled' => true,
                ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MMUserBundle\Entity\MMUserPaymentProfile',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'mmuserbundle_mmuserpaymentprofile_payment';
    }
}
