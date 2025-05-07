<?php

namespace App\Tests\src\Controller\Society;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\User;
use App\Traits\FixturesTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class SocietyGetCollectionControllerTest extends ApiTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->loadFixturesTrait();
    }
    /**
     * @dataProvider getUsers
     */
    public function testAuthorizedShowCollection(int $hierarchy, bool $is_super): void
    {
        $user_load = null;
        if ($is_super) {
            $user_load = $this->all_fixtures['super'];
        } else {
            $user_load = match ($hierarchy) {
                2 => $this->all_fixtures['admin'],
                3 => $this->all_fixtures['user_activate_society'],
                4 => new User(),
                default => null
            };
        }

        $this->client->loginUser($user_load);
        $this->client->request('GET', '/api/societies');

        if ($is_super) {
            $this->assertResponseIsSuccessful();
        } else {
            $status_code = 0;
            if ($hierarchy == 4) {
                $status_code = 401;
            } else {
                $status_code = 403;
            }

            $this->assertResponseStatusCodeSame($status_code);
        }
    }

    public static function getUsers(): array
    {
        return [
            "super_admin" => ['hierarchy' => 1, 'is_super' => true],
            "admin" => ['hierarchy' => 2, 'is_super' => false],
            "user" => ['hierarchy' => 3, 'is_super' => false],
            'anonymous' => ['hierarchy' => 4, 'is_super' => false]
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
