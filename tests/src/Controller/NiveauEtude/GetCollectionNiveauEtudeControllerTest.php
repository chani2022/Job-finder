<?php

namespace App\Tests\src\Controller\NiveauEtude;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;

class GetCollectionNiveauEtudeControllerTest extends ApiTestCase
{
    private ?Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createClient();
    }

    public function testGetCollection(): void
    {
        $this->client->request('GET', '/api/niveau_etude');

        $this->assertResponseIsSuccessful();
    }

    protected function tearDown(): void
    {
        $this->client = null;
    }
}
