<?php

namespace App\Tests\src\Controller\Society;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Traits\FixturesTrait;
use App\Traits\LogUserTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class SocietyPostControllerTest extends ApiTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;
    use LogUserTrait;

    private ?Client $client;
    private ?Security $security;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createClient();
        $this->security = static::getContainer()->get(Security::class);
        $this->loadFixturesTrait();
    }

    public function testPostSociety(): void
    {
        /** @var User $user */
        $user = $this->all_fixtures['user_1'];
        $this->client->loginUser($user);

        $this->client->request("POST", "/api/societies", [
            "json" => [
                "nom_society" => 'test'
            ]
        ]);

        $this->assertJsonContains([
            "nom_society" => "TEST",
            "users" => [
                [
                    "id" => $user->getId()
                ]
            ]
        ]);
    }

    public function testUniqueSociety(): void
    {
        /** @var User $user */
        $user = $this->all_fixtures['user_1'];
        $this->client->loginUser($user);

        $this->client->request("POST", "/api/societies", [
            "json" => [
                "nom_society" => 'unique'
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testNotBlankNomSociety(): void
    {
        /** @var User $user */
        $user = $this->all_fixtures['user_1'];
        $this->client->loginUser($user);

        $this->client->request("POST", "/api/societies", [
            "json" => [
                "nom_society" => ''
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testUnauthorizedCreateSociety(): void
    {
        $this->client->request("POST", "/api/societies", [
            "json" => [
                "nom_society" => 'test'
            ]
        ]);

        $this->assertResponseStatusCodeSame(401);
    }



    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->security = null;
    }
}
