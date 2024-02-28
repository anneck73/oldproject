<?php
/**
 * Copyright 2016-2017 MealMatch UG
 *
 * Author: Wizard <wizard@mealmatch.de>
 * Created: 17.02.18 07:47
 */

namespace Tests\MMWebFrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class CityMealControllerTest extends WebTestCase
{
    /** @var Client $client */
    private $client;
    public function setUp()
    {
        // $this->client = static::createClient();
        $this->client = static::createClient(
            [],
            []
        );
    }

    public function testShowMealsByCityAction()
    {
        /** @var Crawler $crawler */
        $crawler = $this->client->request(
            'GET',
            '/p/social-dining/DE/Köln'
        );

        $response = $this->client->getResponse();
        $this->assertNotNull($response, '->Response was NULL!');
        $this->assertEquals(
            '200',
            $response->getStatusCode(),
            'ERROR->'.$response->getStatusCode().
            'HTML->'.$response->getContent()
        );

        $content = $response->getContent();
        $this->assertNotNull($content, '->Content was NULL!');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Köln")')->count(),
            'City war nicht Köln!'
        );

    }

    public function testShowMealNotFoundByCityAction()
    {
        /** @var Crawler $crawler */
        $crawler = $this->client->request(
            'GET',
            'p/social-dining/DE/XXX'
        );

        $response = $this->client->getResponse();
        $this->assertNotNull($response, '->Response was NULL!');
        $this->assertNotContains('meal-grid-item', $this->client->getResponse()->getContent());
    }

}
