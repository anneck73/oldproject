<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class MMUserProfileType does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class MMUserProfileTypePictureOnly extends AbstractType
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
                'imageFile',
                FileType::class,
                array(
                    static::REQUIRED => true,
                    static::LABEL => false,
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    'attr' => array(
                        'class' => 'hidden',
                        'onChange' => 'this.form.submit()',
                    ),
                )
            );
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
        return 'mmuserbundle_mmuserprofile_pic';
    }
}
