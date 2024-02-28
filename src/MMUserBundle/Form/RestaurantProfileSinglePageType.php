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
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RestaurantProfileSinglePageType extends AbstractType
{
    const LABEL = 'label';
    const TRANSLATION_DOMAIN = 'translation_domain';
    const REQUIRED = 'required';
    const BUNDLE_NAME = 'Mealmatch';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'mmuserbundle_mmrestaurantprofile_single_page_type';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'companyName',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.company.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                    'attr' => array('class' => 'tbox'),
                )
            )
            ->add(
                'name',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.name.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                    'attr' => array('class' => 'tbox'),
                )
            )
            ->add(
                'ownerBirthday',
                BirthdayType::class,
                array(
                    static::LABEL => 'profile.registration.birthday.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                    'attr' => array('class' => 'tbox date-pkr'),
                )
            )
            ->add(
                'ownerCountry',
                CountryType::class,
                array(
                    static::LABEL => 'profile.registration.country.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                    'attr' => array('class' => 'tbox'),
                )
            )
            ->add(
                'ownerNationality',
                CountryType::class,
                array(
                    static::LABEL => 'profile.registration.nationality.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                    'attr' => array('class' => 'tbox'),
                )
            )
            ->add(
                'taxID',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.taxid.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                    'attr' => array('class' => 'tbox'),
                )
            )
            ->add(
                'commercialRegisterNumber',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.regnumber.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                    'attr' => array('class' => 'tbox'),
                )
            )
            /*->add(
                'locationString',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.locationstring.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                    'attr' => array('class' => 'tbox'),
                )
            )
//            ->add(
//                'holderName',
//                TextType::class,
//                array(
//                    static::LABEL => 'restaurantprofile.authorized.label',
//                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
//                    static::REQUIRED => false,
//                    'attr' => array('class' => 'tbox'),
//                )
//            )
            )*/
            ->add(
                'firstName',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.hostLegal.firstName',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                    'attr' => array('class' => 'tbox'),
                ))
            ->add(
                'lastName',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.hostLegal.lastName',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                    'attr' => array('class' => 'tbox'),
                ))
            ->add(
                'contactPhone',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.contactphone.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                    'attr' => array('class' => 'tbox'),
                )
            )
            ->add(
                'bankIBAN',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.iban.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                    'attr' => array('class' => 'tbox'),
                )
            )
            ->add(
                'defaultCurrency',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.default-currency.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                    'attr' => array('class' => 'tbox'),
                )
            )
            ->add(
                'taxRate',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.taxrate.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                    'attr' => array('class' => 'tbox'),
                )
            )
//            ->add(
//                'contactAddress',
//                TextType::class,
//                array(
//                    static::LABEL => 'restaurantprofile.contactaddress.label',
//                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
//                    static::REQUIRED => true,
//                    'attr' => array('class' => 'tbox'),
//                )
//            )
            ->add(
                'addressLine1',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.hostLegal.addressline1',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                    'attr' => array('class' => 'tbox'),
                ))
            ->add(
                'addressLine2',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.hostLegal.addressline2',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                    'attr' => array('class' => 'tbox'),
                ))
            ->add(
                'legalRepresentativeCity',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.hostLegal.city',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                    'attr' => array('class' => 'tbox'),
                ))
            ->add(
                'legalRepresentativePostalCode',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.hostLegal.postalcode',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                    'attr' => array('class' => 'tbox'),
                ))
            ->add(
                'legalRepresentativeRegion',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.hostLegal.region',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                    'attr' => array('class' => 'tbox'),
                ))
            ->add(
                'contactEmail',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.contactemail.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                    'attr' => array('class' => 'tbox'),
                )
            )
            ->add(
                'contactAddress',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.contactaddress.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                    'attr' => array('class' => 'tbox'),
                )
            )
            ->add(
                'contactEmail',
                TextType::class,
                array(
                    static::LABEL => 'restaurantprofile.contactemail.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => true,
                    'attr' => array('class' => 'tbox'),
                )
            )
            ->add(
                'description',
                CKEditorType::class,
                array(
                    static::LABEL => 'restaurantprofile.description.label',
                    static::TRANSLATION_DOMAIN => static::BUNDLE_NAME,
                    static::REQUIRED => false,
                    'config_name' => 'my_config',
                    'attr' => array('class' => 'tbox'),
                )
            );
    }
}
