<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Form;

use MangoPay\KycDocumentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class MMUserProfileType does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class KycDocumentTypeAA extends AbstractType
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
        //base64_decode();
        $builder
            ->add(
                'kycDocType',
                ChoiceType::class,
                array(
                    'choices' => array(
                        'kyc.docType.articleAss' => KycDocumentType::ArticlesOfAssociation,
                    ),
                    static::LABEL => 'kyc.document',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                )
            )

            ->add(
                'kycDocCode',
                FileType::class,
                array(
                    'constraints' => array(
                        new All(array(
                            'constraints' => array(
                                new File(array(
                                    'maxSize' => '7M',
                                )),
                            ),
                        )),
                    ),
                    'multiple' => true,
                    static::LABEL => 'kyc.file',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,

                    //'action' => 'readURL(kycDocCode)',
                )
            )

            ->add('kycDocSubmitted', HiddenType::class, array(
                'data' => 'ARTICLES_OF_ASSOCIATION',
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MMUserBundle\Entity\MMUserKYCProfile',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'kyc_document_type_aa';
    }
}
