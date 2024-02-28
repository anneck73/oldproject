<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
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
class MMUserProfileTypeBasic extends AbstractType
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
                    static::REQUIRED => true,
                )
            )
            ->add(
                'lastName',
                TextType::class,
                array(
                    static::LABEL => 'profile.registration.last_name',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                )
            )
            ->add(
                'birthday',
                BirthdayType::class,
                array(
                    static::LABEL => 'profile.registration.birthday.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                )
            )
            ->add(
                'nationality',
                CountryType::class,
                array(
                    static::LABEL => 'profile.registration.nationality',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                )
            )
            ->add(
                'gender',
                ChoiceType::class,
                array(
                    'choices' => array(
                        'profile.registration.female.label' => 'F',
                        'profile.registration.male.label' => 'M',
                    ),
                    static::REQUIRED => true,
                    static::LABEL => 'profile.registration.gender',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    'choice_translation_domain' => static::BUNDLE_NAME,
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
        return 'mmuserbundle_mmuserprofile_basic';
    }
}
