<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMWebFrontBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class ContactFormType does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class InviteAFriendFormType extends AbstractType
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
        $builder
            ->add(
                'email',
                EmailType::class,
                array(
                    'attr' => array('placeholder' => '...'),
                    'constraints' => array(
                        new NotBlank(array('message' => 'Please provide a valid email')),
                        new Email(array('message' => "The email doesn't seems to be valid")),
                    ),
                    'invalid_message' => 'invite.form.email.error',
                    'label' => 'invite.form.email.label',
                    'translation_domain' => 'Mealmatch',
                    'required' => true,
                )
            );
    }

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'error_bubbling' => true,
            )
        );
    }

    public function getName()
    {
        return 'invite_form';
    }
}
