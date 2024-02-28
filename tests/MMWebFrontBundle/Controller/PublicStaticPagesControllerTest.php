<?php
/**
 * Copyright (c) 2017. Mealmatch GmbH
 * Author: Wizard <wizard@mealmatch.de>
 */

namespace MMWebFrontBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\DomCrawler\Crawler;

class PublicStaticPagesControllerTest extends WebTestCase
{

    public function testIndexHTML()
    {

        /** @var Client $client */
        $client = static::createClient();
        /** @var Crawler $crawler */
        $crawler = $client->request('GET', '/');

        $response = $client->getResponse();
        $this->assertNotNull($response, '->Response was NULL!');

        $content = $response->getContent();
        $this->assertNotNull($content, '->Content was NULL!');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Suche interessante Meals in deiner Stadt")')->count()
        );
    }

    public function testPressHTML()
    {

        /** @var Client $client */
        $client = static::createClient();
        /** @var Crawler $crawler */
        $crawler = $client->request('GET', '/presse');

        $response = $client->getResponse();
        $this->assertNotNull($response, '->Response was NULL!');

        $content = $response->getContent();
        $this->assertNotNull($content, '->Content was NULL!');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Mealmatch Social-Dining")')->count()
        );
    }

    public function testAboutHTML()
    {

        /** @var Client $client */
        $client = static::createClient();
        /** @var Crawler $crawler */
        $crawler = $client->request('GET', '/about');

        $response = $client->getResponse();
        $this->assertNotNull($response, '->Response was NULL!');

        $content = $response->getContent();
        $this->assertNotNull($content, '->Content was NULL!');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Mealmatch Social-Dining")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Über Mealmatch")')->count()
        );
    }

    public function testImprintHTML()
    {

        /** @var Client $client */
        $client = static::createClient();
        /** @var Crawler $crawler */
        $crawler = $client->request('GET', '/imprint');

        $response = $client->getResponse();
        $this->assertNotNull($response, '->Response was NULL!');

        $content = $response->getContent();
        $this->assertNotNull($content, '->Content was NULL!');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Mealmatch GmbH")')->count(),
            'HTML is missing correct legal issue!!!'
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("DE308440126")')->count()
        );
    }

    public function testBecomeHostHTML()
    {

        /** @var Client $client */
        $client = static::createClient();
        /** @var Crawler $crawler */
        $crawler = $client->request('GET', '/becomeHost');

        $response = $client->getResponse();
        $this->assertNotNull($response, '->Response was NULL!');

        $content = $response->getContent();
        $this->assertNotNull($content, '->Content was NULL!');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Werde ein Gastgeber")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Mealmatch Social-Dining")')->count()
        );
    }

    public function testGetHelpHTML()
    {

        /** @var Client $client */
        $client = static::createClient();
        /** @var Crawler $crawler */
        $crawler = $client->request('GET', '/help');

        $response = $client->getResponse();
        $this->assertNotNull($response, '->Response was NULL!');

        $content = $response->getContent();
        $this->assertNotNull($content, '->Content was NULL!');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Hilfe")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Mealmatch Social-Dining")')->count()
        );
    }

    public function testTermsHTML()
    {

        /** @var Client $client */
        $client = static::createClient();
        /** @var Crawler $crawler */
        $crawler = $client->request('GET', '/terms');

        $response = $client->getResponse();
        $this->assertNotNull($response, '->Response was NULL!');

        $content = $response->getContent();
        $this->assertNotNull($content, '->Content was NULL!');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Nutzungsbedingungen")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Mealmatch Social-Dining")')->count()
        );
    }

    public function testTermsRestaurantHTML()
    {

        /** @var Client $client */
        $client = static::createClient();
        /** @var Crawler $crawler */
        $crawler = $client->request('GET', '/terms');

        $this->assertTrue(
            $client->getResponse()->isSuccessful(),
            $client->getResponse()->getContent()
        );

        $response = $client->getResponse();
        $this->assertNotNull($response, '->Response was NULL!');

        $content = $response->getContent();
        $this->assertNotNull($content, '->Content was NULL!');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Restaurant")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Mealmatch Social-Dining")')->count()
        );
    }

    public function testTermsMangopayHTML()
    {

        /** @var Client $client */
        $client = static::createClient();
        /** @var Crawler $crawler */
        $crawler = $client->request('GET', '/mangopay/terms');

        $this->assertTrue(
            $client->getResponse()->isSuccessful(),
            $client->getResponse()->getContent()
        );

        $response = $client->getResponse();
        $this->assertNotNull($response, '->Response was NULL!');

        $content = $response->getContent();
        $this->assertNotNull($content, '->Content was NULL!');

        $this->assertContains('Mangopay.pdf', $client->getResponse()->getContent());

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Mealmatch Social-Dining")')->count()
        );
    }
}
