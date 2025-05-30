<?php

namespace App\Tests\src\Security\RefreshToken;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use ApiPlatform\Symfony\Bundle\Test\Response;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class RefreshTokenTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    private ?Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    public function testPostRefreshToken(): void
    {
        /** @var Response */
        $response = $this->client->request('POST', '/api/login_check', [
            'json' => [
                'username' => 'adm',
                'password' => 'adm'
            ]
        ]);
        $this->assertResponseIsSuccessful();

        $refresh_token = $response->getBrowserKitResponse()->toArray()['refresh_token'];
        /** @var Response */
        $response = $this->client->request('POST', '/api/token/refresh', [
            'json' => [
                'refresh_token' => $refresh_token
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $data = $response->getBrowserKitResponse()->toArray();

        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
    }
}
