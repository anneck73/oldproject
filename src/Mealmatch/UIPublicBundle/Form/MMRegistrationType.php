<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\UIPublicBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class MMRegistrationType does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class MMRegistrationType extends AbstractType
{
    const LABEL = 'label';
    const TRANSLATION_DOMAIN = 'translation_domain';
    const REQUIRED = 'required';
    const BUNDLE_NAME = 'Mealmatch';
    const PLACEHOLDER_HTML_ATTR = 'placeholder';

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     * @param string               $myArgument with a *description* of this argument, these may also
     *                                         span multiple lines
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'email',
            EmailType::class,
            array(
                'attr' => array('class' => 'form-control tbox', self::PLACEHOLDER_HTML_ATTR => 'Enter your email'),
                static::LABEL => 'form.email',
                static::TRANSLATION_DOMAIN => 'Mealmatch',
                static::REQUIRED => true,
            )
        )->add(
            'username',
            TextType::class,
            array(
                'attr' => array('class' => 'form-control tbox', self::PLACEHOLDER_HTML_ATTR => 'Enter your Username'),
                static::LABEL => 'form.username',
                static::TRANSLATION_DOMAIN => 'Mealmatch',
                static::REQUIRED => true,
            )
        )->add('password', RepeatedType::class, array(
            'type' => PasswordType::class,
            'first_options' => array('label' => 'Password', 'attr' => array('class' => 'form-control tbox', self::PLACEHOLDER_HTML_ATTR => 'Enter Password')),
            'second_options' => array('label' => 'Repeat Password', 'attr' => array('class' => 'form-control tbox', self::PLACEHOLDER_HTML_ATTR => 'Confirm Password')),
            'invalid_message' => 'The password fields must match.',
            )
        )->add(
            'termsAccepted',
            CheckboxType::class,
            array(
                static::LABEL => 'terms.text',
                static::TRANSLATION_DOMAIN => 'Mealmatch',
            )
        )->add(
            'over18',
            CheckboxType::class,
            array(
                static::LABEL => 'over18.text',
                static::TRANSLATION_DOMAIN => 'Mealmatch',
            )
        )
        ;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return string
     */
    /*public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
    }*/

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'ui_app_user_registration';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MMUserBundle\Entity\MMUser',
            )
        );
    }
}
