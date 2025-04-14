<?php

namespace App\Tests\src\Controller\User;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use ApiPlatform\Symfony\Bundle\Test\Response;
use App\Entity\User;
use App\Traits\FixturesTrait;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class LoginControllerTest extends ApiTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;

    private ?Client $client = null;
    private ?EntityManagerInterface $em = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient([], [
            "headers" => [
                "content-type" => "application/json",
                "accept" => "application/json"
            ]
        ]);

        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $this->loadFixturesTrait();
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

    public function testUserDisabled(): void
    {
        $user_disabled = $this->all_fixtures['user_disabled'];

        $user_bdd = $this->em->getRepository(User::class)->find($user_disabled->getId());
        $user_bdd->setStatus(false);

        $this->em->flush();
        /** @var Response */
        $response = $this->client->request("POST", "/api/login_check", [
            "json" => [
                "username" => "disabled@test.com",
                "password" => "test"
            ]
        ]);

        $excepted = $response->getBrowserKitResponse()->toArray();
        $this->assertEquals(["code" => 401, "message" => "Votre compte est désactivé."], $excepted);
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
        $this->all_fixtures = null;

        // Réinitialise le kernel entre les tests
        static::ensureKernelShutdown();
    }
}
