<?php
/**
 * Copyright (c) 2017. Mealmatch GmbH
 * Author: Wizard <wizard@mealmatch.de>
 */

namespace MMWebFrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class DefaultControllerTest does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class DefaultControllerTest extends WebTestCase
{

    public function testSimpleSearchThreeCategoriesHTML()
    {
        static::markTestSkipped('Test needs re-write!');
        /** @var Client $client */
        $client = static::createClient();
        /** @var Crawler $crawler */
        $crawler = $client->request(
            'GET',
            '/s/?searchLocation=Berlin&datetime=30.12.2016+20%3A00&searchCategory=Deutsch%2CIndisch%2CAfrikanisch'
        );

        /** @var Response $response */
        $response = $client->getResponse();
        $this->assertNotNull($response, '->Response was NULL!');
        $this->assertEquals(
            '200',
            $response->getStatusCode(),
            'ERROR->'.$response->getStatusCode().
            'HTML->'.$client->getResponse()->getContent()
        );

        $content = $response->getContent();
        $this->assertNotNull($content, '->Content was NULL!');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Deutsch")')->count(),
            'Kategorie Deutsch nicht enthalten!'
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Indisch")')->count(),
            'Kategorie Indisch nicht enthalten!'
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Afrikanisch")')->count(),
            'Kategorie Afrikanisch nicht enthalten!'
        );
    }

    public function testSimpleSearchTwoCategoriesHTML()
    {
        static::markTestSkipped('Test needs re-write!');

        /** @var Client $client */
        $client = static::createClient();
        /** @var Crawler $crawler */
        $crawler = $client->request(
            'GET',
            '/s/?searchLocation=Berlin&datetime=30.12.3016+20%3A00&searchCategory=Deutsch%2CIndisch'
        );

        $response = $client->getResponse();
        $this->assertNotNull($response, '->Response was NULL!');

        $content = $response->getContent();
        $this->assertNotNull($content, '->Content was NULL!');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Deutsch")')->count(),
            'Kategorie Deutsch nicht enthalten!'
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Indisch")')->count(),
            'Kategorie Indisch nicht enthalten!'
        );
    }

    public function testSimpleSearchOneCategoryHTML()
    {
        static::markTestSkipped('Test needs re-write!');
        /** @var Client $client */
        $client = static::createClient();
        /** @var Crawler $crawler */
        $crawler = $client->request(
            'GET',
            '/s/?searchLocation=Berlin&datetime=30.12.3017+20%3A00&searchCategory=Deutsch'
        );

        $response = $client->getResponse();
        $this->assertNotNull($response, '->Response was NULL!');

        $content = $response->getContent();
        $this->assertNotNull($content, '->Content was NULL!');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Deutsch")')->count(),
            'Kategorie Deutsch nicht enthalten!'
        );
    }

    public function testSimpleLocationSearchHTML()
    {
        static::markTestSkipped('Test needs re-write!');

        /** @var Client $client */
        $client = static::createClient();
        /** @var Crawler $crawler */
        $crawler = $client->request('GET', '/s/?searchLocation=Berlin');

        $response = $client->getResponse();
        $this->assertNotNull($response, '->Response was NULL!');

        $content = $response->getContent();
        $this->assertNotNull($content, '->Content was NULL!');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Ort: Berlin")')->count(),
            'HTTP STATUS: '.$response->getStatusCode().' HTML CONTENT: '.$response->getContent()
        );
    }


    /**
     * ServiceDataManagerTest case to ensure valid json is returned.
     */
    public function testCategoriesJSON()
    {
        static::markTestSkipped('Test needs re-write!');
        /** @var Client $client */
        $client = static::createClient();
        $client->request('GET', '/categories');
        $response = $client->getResponse();
        $this->assertNotNull($response, 'Categories->Response was NULL!');

        $content = $response->getContent();
        $this->assertNotNull($content, 'Categories->Content was NULL!');
        $this->isJson($content, 'Categories->Result is not JSON!');

        $json = json_decode($content);

        $this->assertNotNull($json, 'Categories->json_decode failed!');

    }

}
