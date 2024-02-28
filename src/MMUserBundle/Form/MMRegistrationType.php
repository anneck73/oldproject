<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class MMRegistrationType does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class MMRegistrationType extends AbstractType
{
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
            'termsAccepted',
            null,
            array(
                'label' => 'terms.text',
                'translation_domain' => 'Mealmatch',
            )
        )->add(
            'over18',
            null,
            array(
                'label' => 'over18.text',
                'translation_domain' => 'Mealmatch',
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
    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
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
    public function getBlockPrefix()
    {
        return 'app_user_registration';
    }
}
