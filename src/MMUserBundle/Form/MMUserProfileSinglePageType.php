<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Form;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
class MMUserProfileSinglePageType extends AbstractType
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
                    'attr' => array('class' => 'form-control tbox'),
                    static::LABEL => 'profile.registration.first_name',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                )
            )
            ->add(
                'lastName',
                TextType::class,
                array(
                    'attr' => array('class' => 'form-control tbox'),
                    static::LABEL => 'profile.registration.last_name',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                )
            )
//            ----------------------------Using MMUserProfileTypePicOnly--------
//            ->add(
//                'imageFile',
//                FileType::class,
//                array(
//                    static::REQUIRED => false,
//                    static::LABEL => 'profile.registration.profile_photo',
//                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
//                )
//            )
            ->add('country',
                CountryType::class,
                array(
                    'attr' => array('class' => 'form-control tbox'),
                    static::REQUIRED => true,
                    static::LABEL => 'profile.registration.country',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                )
            )
            ->add('nationality',
                CountryType::class,
                array(
                    'attr' => array('class' => 'form-control tbox'),
                    'empty_data' => '---',
                    static::REQUIRED => true,
                    static::LABEL => 'profile.registration.nationality',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                )
            )
            ->add(
                'postalAddress',
                TextType::class,
                array(
                    'attr' => array('class' => 'form-control tbox'),
                    static::REQUIRED => true,
                    static::LABEL => 'profile.registration.postalAddress',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                )
            )
            ->add(
                'birthday',
                BirthdayType::class,
                array(
                    'attr' => array('class' => 'form-control tbox'),
                    static::LABEL => 'profile.registration.birthday',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                    'widget' => 'single_text',
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
                    'attr' => array('class' => 'form-control tbox'),
                    static::REQUIRED => true,
                    'placeholder' => 'profile.registration.gender',
                    static::LABEL => 'profile.registration.gender',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    'choice_translation_domain' => static::BUNDLE_NAME,
                )
            )
            ->add(
                'selfDescription',
//               Should we better using CkEditorType?
//                CKEditorType::class,
                TextareaType::class,
                array(
                    'attr' => array('class' => 'form-control tbox'),
                    static::LABEL => 'profile.registration.selfdescription',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
//                    'config_name' => 'my_config',
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
