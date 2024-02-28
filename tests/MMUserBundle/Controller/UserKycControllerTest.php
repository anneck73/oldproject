<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2.15-dev
 */

namespace tests\MMUserBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DomCrawler\Field\FileFormField;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class UserKycControllerTest
 *
 * @package tests\MMUserBundle\Controller
 *
 */
class UserKycControllerTest extends KernelTestCase {

    /* @var Client */
    public $client;
    public $container;
    public $entityManager;

    public function setUp() {
        $kernel = self::bootKernel();
        $this->container = $kernel->getContainer();
        $this->client = $this->container->get('test.client');
        $this->entityManager = $this->container->get('doctrine.orm.default_entity_manager');
    }

    public function logIn($userName, $password)
    {
        $this->client->request('GET', '/login');
        if ($this->client->getResponse()->isRedirection()) {
            $this->client->followRedirect();
        }
        $form = $this->client->getCrawler()->selectButton('Anmelden')->form();
        $form['_username'] = $userName;
        $form['_password'] = $password;
        $this->client->submit($form);
        $this->client->followRedirect();
    }

    public function testShowManager() {
        $this->client->request(
            'GET',
            'en/u/profile/kyc/show');
        $this->client->getResponse();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test KYC document upload for guest
     *
     * @dataProvider provideGuestData
     *
     */
    public function guestKyc($files) {
        $this->logIn('MMTestGuest', 'MMTest');
        static $check = 0;
        $parameters = array(
            'kycDocType' => 'ID Card',
            'kycDocSubmitted' => 'IDENTITY_PROOF',);

        $crawler = $this->client->request(
            'POST',
            'en/u/profile/kyc/guest',
            $parameters);
        $count = $this->client->getCrawler()->filter('h1')->count();

        if('2' == $count) {
            $form = $this->client->getCrawler()->selectButton('Submit')->form();
            $form['kyc_document_type[kycDocCode]'] = $files;
            $this->client->submit($form);

            if( 0 === $check ) {
                $alertMsg = $this->client->getCrawler()->filter('.alert-danger')->text();
                $this->assertNotEquals(null, $alertMsg);
                $check++;
            } else {
                $count = $this->client->getCrawler()->filter('Submit')->count();
                $this->assertEquals(0, $count);
                //Test hooks for guest kyc
                $kyc = $this->entityManager->getRepository('MMUserBundle:MMUserKYCProfile')->findAll();
                $guestKyc = end($kyc);
                $this->hooksTest('KYC_FAILED', $guestKyc->getKycId());
            }

        } else {
            $this->assertEquals(3, $count);
        }

    }

    /**
     * Test KYC document upload for host
     *
     * @dataProvider provideHostData
     * @test
     */
    public function hostKyc($selectedTab, $files) {

        $this->logIn('MMTestRestaurant', 'MMTest');
        $parameters = array (
            'selectedTab' => $selectedTab,);

        $crawler = $this->client->request(
            'POST',
            'en/u/profile/kyc/host_id',
            $parameters);
        if ($this->client->getResponse()->isRedirection()) {
            $this->client->followRedirect();
        }
        $count = $this->client->getCrawler()->selectButton('Submit')->count();
        if($count) {
            $form = $this->client->getCrawler()->selectButton('Submit')->form();

            if ('1' == $selectedTab) {
                $count = $this->client->getCrawler()->filter('#status1')->count();
                if ('0' == $count) {
                    $node = $this->client->getCrawler()->filter("input[name='kyc_document_type_id[kycDocCode][]']")->getNode(0);
                    // add additional fields to form (you can create as many as you need)
                    $newField = new FileFormField($node);
                    $form->set($newField);
                    // set files with upload()
                    $form['kyc_document_type_id[kycDocCode]'][0]->upload($files[0]);
                    $form['kyc_document_type_id[kycDocCode]'][1]->upload($files[1]);
                    $form['kyc_document_type_id[kycDocType]'] = 'ID Card';
                    $this->client->submit($form);
                    $this->client->followRedirect();
                    $count = $this->client->getCrawler()->filter('#status1')->count();
                }
            } elseif ('2' == $selectedTab) {
                $count = $this->client->getCrawler()->filter('#status2')->count();
                if ('0' == $count) {
                    $form['kyc_document_type_rp[kycDocCode]'] = $files;
                    $this->client->submit($form);
                    $this->client->followRedirect();
                    $count = $this->client->getCrawler()->filter('#status2')->count();
                }
            } elseif ('3' == $selectedTab) {
                $count = $this->client->getCrawler()->filter('#status3')->count();
                if ('0' == $count) {
                    $form['kyc_document_type_aa[kycDocCode]'] = $files;
                    $this->client->submit($form);
                    $this->client->followRedirect();
                    $count = $this->client->getCrawler()->filter('#status3')->count();
                }
            } else {
                $count = $this->client->getCrawler()->filter('#status4')->count();
                if ('0' == $count) {
                    $form['kyc_document_type_sd[kycDocCode]'] = $files;
                    $this->client->submit($form);
                    $this->client->followRedirect();
                    $count = $this->client->getCrawler()->filter('#status4')->count();
                }
            }
            $this->assertEquals(1, $count);

        } else{
            $this->assertEquals(0, $count);
        }
    }

    /**
     * Provides data to check hooks
     * @test
     */
    public function provideHookData() {
        $kyc = $this->entityManager->getRepository('MMUserBundle:MMUserKYCProfile')->findAll();
        $this->hooksTest('KYC_SUCCEEDED', $kyc[0]->getKycId());
        $this->hooksTest('KYC_SUCCEEDED', $kyc[1]->getKycId());
        $this->hooksTest('KYC_SUCCEEDED', $kyc[2]->getKycId());
        $this->hooksTest('KYC_SUCCEEDED', $kyc[3]->getKycId());
    }

    /*
     * Test the KYC hooks (KYC_SUCCEEDED, KYC_FAILED)
     */
    public function hooksTest($eventType, $resourceId) {

        $this->client->request(
            'GET',
            '/u/profile/kyc/kychooks?EventType='.$eventType.'&RessourceId='.$resourceId.'');
        if ($this->client->getResponse()->isRedirection()) {
            $this->client->followRedirect();
        }
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /*
     * Data provider for guestKyc
     */
    public function provideGuestData() {

        return array(
            array(
            //file size greater than the limit 7Mb
            array(new UploadedFile(
                __DIR__.'/../Resources/public/pdf/kycErrorTestPDF.pdf',
                'kycErrorTestPDF.pdf',
                'application/pdf',
                null,
                0,
                true
            ),),),
            array(
            //test with jpeg file
            array(new UploadedFile(
                __DIR__.'/../Resources/public/images/kycTest.jpeg',
                'kycTest.jpeg',
                'image/jpeg',
                null,
                0,
                true
            ),),)
        );
    }

    /*
     * Data provider for hostKyc
     */
    public function provideHostData()
    {
        return array(
            //test multiple file uploading
            array(1, array(new UploadedFile(
                __DIR__.'/../Resources/public/images/kycPngTest.png',
                'kycPngTest.png',
                'image/png',
                null,
                0,
                true
            ), new UploadedFile(
                __DIR__.'/../Resources/public/images/kycPngTest.png',
                'kycPngTest.png',
                'image/png',
                null,
                0,
                true
            ),)),
            //test with jpeg file
            array(2, array(new UploadedFile(
                __DIR__.'/../Resources/public/images/kycTest.jpeg',
                'kycTest.jpeg',
                'image/jpeg',
                null,
                0,
                true
            ),)),
            //test with jpg file
            array(3, array(new UploadedFile(
                __DIR__.'/../Resources/public/images/kycTest1.jpg',
                'kycTest1.jpg',
                'image/jpg',
                null,
                0,
                true
            ),)),
            //test with pdf file
            array(4, array(new UploadedFile(
                __DIR__.'/../Resources/public/pdf/kycTestPdf.pdf',
                'kycTestPdf.pdf',
                'application/pdf',
                null,
                0,
                true
            ),)),
        );
    }
}
