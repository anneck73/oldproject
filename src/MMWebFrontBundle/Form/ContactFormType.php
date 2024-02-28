<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMWebFrontBundle\Form;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
class ContactFormType extends AbstractType
{
    const PLACEHOLDER_HTML_ATTR = 'placeholder';
    const HTML_ATTR_CONSTRAINTS = 'constraints';

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
                'name',
                TextType::class,
                array(
                    'attr' => array(self::PLACEHOLDER_HTML_ATTR => 'contactform.name'),
                    self::HTML_ATTR_CONSTRAINTS => array(
                        new NotBlank(),
                    ),
                    'translation_domain' => 'Mealmatch',
                )
            )
            ->add(
                'email',
                EmailType::class,
                array(
                    'attr' => array(self::PLACEHOLDER_HTML_ATTR => 'contactform.addr'),
                    self::HTML_ATTR_CONSTRAINTS => array(
                        new NotBlank(),
                        new Email(),
                    ),
                    'translation_domain' => 'Mealmatch',
                )
            )
            ->add(
                'subject',
                TextType::class,
                array(
                    'attr' => array(self::PLACEHOLDER_HTML_ATTR => 'contactform.subject'),
                    self::HTML_ATTR_CONSTRAINTS => array(
                        new NotBlank(),
                    ),
                    'label' => 'contact.form.subject.label',
                    'translation_domain' => 'Mealmatch',
                    'required' => true,
                )
            )
            ->add(
                'message',
                CKEditorType::class,
                array(
                    'config_name' => 'my_config',
                    'attr' => array(self::PLACEHOLDER_HTML_ATTR => 'contact.form.message.placeholder'),
                    self::HTML_ATTR_CONSTRAINTS => array(new NotBlank()),
                    'invalid_message' => 'contact.form.message.error',
                    'label' => 'contact.form.message.label',
                    'translation_domain' => 'Mealmatch',
                    'required' => true,
                )
            )
        ;
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
        return 'contact_form';
    }
}
