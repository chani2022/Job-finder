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

        $response = $this->client->request("POST", "/api/societies", [
            "json" => [
                "nom_society" => 'test'
            ]
        ]);


        $user = static::getContainer()->get(EntityManagerInterface::class)->getRepository(User::class)->find($user->getId());

        $this->assertEquals(['ROLE_ADMIN', 'ROLE_USER'], $user->getRoles());

        $responseData = $response->toArray();

        $this->assertArrayHasKey('users', $responseData);
        $this->assertIsArray($responseData['users']);
        $this->assertEquals("TEST", $responseData['nom_society']);
        // Cherche un utilisateur avec le bon ID
        $found = false;
        foreach ($responseData['users'] as $userData) {
            if (
                $userData['id'] === $user->getId() &&
                $userData['username'] === $user->getUsername() &&
                $userData['roles'] === ['ROLE_ADMIN', 'ROLE_USER']
            ) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'L\'utilisateur attendu n\'a pas été trouvé dans la collection "users".');
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
