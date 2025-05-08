<?php

namespace App\Tests\src\Controller\User;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;

class GetCollectionUserControllerTest extends ApiTestCase
{
    private ?Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    public function testGetCollectionUser(): void
    {
        $this->client->request('GET', '/api/users');

        $this->assertResponseIsSuccessful();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
    }
}
