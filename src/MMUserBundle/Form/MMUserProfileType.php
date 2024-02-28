<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class MMUserProfileType does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class MMUserProfileType extends AbstractType
{
    const LABEL = 'label';
    const TRANSLATION_DOMAIN = 'translation_domain';
    const REQUIRED = 'required';
    const BUNDLE_NAME = 'Mealmatch';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'firstName',
                TextType::class,
                array(
                    static::LABEL => 'profile.registration.first_name',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'lastName',
                TextType::class,
                array(
                    static::LABEL => 'profile.registration.last_name',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'imageFile',
                FileType::class,
                array(
                    static::REQUIRED => false,
                    static::LABEL => 'profile.registration.profile_photo',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                )
            )
            ->add(
                'addressLine1',
                TextType::class,
                array(
                    static::LABEL => 'profile.registration.addressline1',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'addressLine2',
                TextType::class,
                array(
                    static::LABEL => 'profile.registration.addressline2',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'areaCode',
                IntegerType::class,
                array(
                    static::LABEL => 'profile.registration.areacode',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'city',
                TextType::class,
                array(
                    static::LABEL => 'profile.registration.city',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'state',
                TextType::class,
                array(
                    static::LABEL => 'profile.registration.state',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'country',
                CountryType::class,
                array(
                    static::LABEL => 'profile.registration.country',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'nationality',
                CountryType::class,
                array(
                    static::LABEL => 'profile.registration.nationality',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'age',
                IntegerType::class,
                array(
                    static::LABEL => 'profile.registration.age',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'gender',
                ChoiceType::class,
                array(
                    'choices' => array(
                        'profile.registration.male.label' => 'M',
                        'profile.registration.female.label' => 'F',
                    ),
                    static::REQUIRED => false,
                    'placeholder' => 'profile.registration.gender',
                    static::LABEL => 'profile.registration.gender',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    'choice_translation_domain' => static::BUNDLE_NAME,
                )
            )
            ->add(
                'hobbies',
                TextType::class,
                array(
                    static::LABEL => 'profile.registration.hobbies',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'payPalEmail',
                EmailType::class,
                array(
                    static::LABEL => 'profile.registration.paypalemail',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'phone',
                TextType::class,
                array(
                    static::LABEL => 'profile.registration.phone',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
            ->add(
                'selfDescription',
                TextType::class,
                array(
                    static::LABEL => 'profile.registration.selfdescription',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                )
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MMUserBundle\Entity\MMUserProfile',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'mmuserbundle_mmuserprofile';
    }
}
