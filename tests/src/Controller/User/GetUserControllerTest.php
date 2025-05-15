<?php

namespace App\Tests\src\Controller\User;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Traits\FixturesTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use App\Entity\User;

class GetUserControllerTest extends ApiTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;

    private ?Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createClient();
        $this->loadFixturesTrait();
    }
    /**
     * utilisateurs qui peuvent voir le ressource (SUPER_ADMIN, ADMIN que sont society est la même que utilisateur a voir, et l'utilisateur courant)
     * @dataProvider getUser
     * @param string $roles
     * @param bool $can_read
     * 
     */
    public function testGetUser(string $roles, bool $can_read): void
    {
        /** @var User */
        $user_want_access = $this->all_fixtures['user_adm_society'];
        $user_access_resource = match ($roles) {
            'super-admin' => $this->all_fixtures['super'],
            'admin-owner-user' => $this->all_fixtures['admin_adm_society'],
            'user-owner' => $this->all_fixtures['user_adm_society'],
            'admin-other' => $this->all_fixtures['admin_1'],
            'user-other' => $this->all_fixtures['user_1'],
            default => null
        };

        if ($user_access_resource) {
            $this->client->loginUser($user_access_resource);
        }
        $this->client->request('GET', '/api/users/' . $user_want_access->getId());

        $this->assert($roles, $can_read);
    }

    private function assert(string $roles, bool $can_read): void
    {
        if ($can_read) {
            $this->assertResponseIsSuccessful();
        } else {
            $status = 403;
            if ($roles == "anonymous") {
                $status = 401;
            }
            $this->assertResponseStatusCodeSame($status);
        }
    }

    public static function getUser(): array
    {
        return [
            "super-admin" => ['roles' => 'super-admin', 'can_read' => true],
            "admin-owner-user" => ['roles' => 'admin-owner-user', 'can_read' => true],
            "user-owner" => ['roles' => 'user-owner', 'can_read' => true],
            "admin-other" => ['roles' => 'admin-other', 'can_read' => false],
            "user-other" => ['roles' => 'user-other', 'can_read' => false],
            "anonymous" => ['roles' => 'anonymous', 'can_read' => false]
        ];
    }
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
    }
}
