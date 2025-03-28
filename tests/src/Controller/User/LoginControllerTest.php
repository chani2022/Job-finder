<?php

namespace App\Tests\src\Controller\User;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use ApiPlatform\Symfony\Bundle\Test\Response;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class LoginControllerTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    private ?Client $client = null;


    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient([], [
            "headers" => [
                "content-type" => "application/json",
                "accept" => "application/json"
            ]
        ]);
    }
    /**
     * @dataProvider provideCredentialsErrors
     */
    public function testAuthFailed($identifiants): void
    {
        /** @var Response */
        $response = $this->client->request("POST", "/api/login_check", [
            "json" => $identifiants
        ]);
        $infos = $response->getBrowserKitResponse()->toArray();
        $this->assertArrayHasKey("code", $infos);
        $this->assertArrayHasKey("message", $infos);
        $this->assertEquals(401, $infos["code"]);
    }

    /**
     * @dataProvider provideCredentialsValid
     */
    public function testAuthValid($identifiants): void
    {
        /** @var Response */
        $response = $this->client->request("POST", "/api/login_check", [
            "json" => $identifiants
        ]);
        $infos = $response->getBrowserKitResponse()->toArray();
        $this->assertArrayHasKey("token", $infos);
    }

    public static function provideCredentialsErrors(): array
    {
        return [
            "username and password wrong" => [
                ["username" => "wrong", "password" => "wrong"]
            ],
            "username wrong and password ok" => [
                ["username" => "wrong", "password" => "test"]
            ],
            "username ok and password wrong" => [
                ["username" => "test", "password" => "wrong"]
            ],
            "email and password wrong" => [
                ["username" => "wrong@wrong.com", "password" => "wrong"]
            ],
            "email wrong and password ok" => [
                ["username" => "wrong@wrong.com", "password" => "test"]
            ],
            "email ok and password wrong" => [
                ["username" => "test@test.com", "password" => "wrong"]
            ]
        ];
    }

    public static function provideCredentialsValid(): array
    {
        return [
            "username and password ok" => [
                ["username" => "test", "password" => "test"]
            ],
            "email and password ok" => [
                ["username" => "test@test.com", "password" => "test"]
            ]
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;

        // Réinitialise le kernel entre les tests
        static::ensureKernelShutdown();
    }
}
