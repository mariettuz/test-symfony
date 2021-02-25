<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SellerControllerTest extends WebTestCase
{
    public function testRightSeller(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/seller', ['url' => 'https://www.verizonmedia.com']);

        $responseContent = $client->getResponse()->getContent();
        $responseArray = json_decode($responseContent, true);

        $this->assertJson($responseContent);
        $this->assertNotEmpty($responseArray['response']['body']);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testWrongNoUrlSeller(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/seller');

        $responseContent = $client->getResponse()->getContent();
        $responseArray = json_decode($responseContent, true);

        $this->assertJson($responseContent);
        $this->assertEmpty($responseArray['response']['body']);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testWrongUrlSeller(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/seller', ['url' => 'test']);

        $responseContent = $client->getResponse()->getContent();
        $responseArray = json_decode($responseContent, true);

        $this->assertJson($responseContent);
        $this->assertEmpty($responseArray['response']['body']);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testWrongNoJsonSeller(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/seller', ['url' => 'https://www.repubblica.it']);

        $responseContent = $client->getResponse()->getContent();
        $responseArray = json_decode($responseContent, true);

        $this->assertJson($responseContent);
        $this->assertEmpty($responseArray['response']['body']);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
