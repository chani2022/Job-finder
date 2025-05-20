<?php

namespace App\Tests\src\Controller\Experience;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\User;
use App\Traits\FixturesTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class GetCollectionCategoryControllerTest extends ApiTestCase
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
     * @dataProvider getUserAuthorized
     */
    public function testGetCollectionCategory(?string $roles, bool $access): void
    {
        /** @var User */
        $user = $this->getUser($roles);
        if ($user) {
            $this->client->loginUser($user);
        }
        $this->client->request('GET', '/api/categories');

        if ($access) {
            $this->assertResponseIsSuccessful();
        } else {
            $status = 403;
            if (!$roles) {
                $status = 401;
            }

            $this->assertResponseStatusCodeSame($status);
        }
    }

    public static function getUserAuthorized(): array
    {
        return [
            'super' => ['roles' => 'super', 'access' => true],
            'admin' => ['roles' => 'admin', 'access' => false],
            'user' => ['roles' => 'user', 'access' => false],
            'anonymous' => ['roles' => null, 'access' => false]
        ];
    }

    private function getUser(?string $roles): ?User
    {
        /** @var User */
        $user = match ($roles) {
            'super' => $this->all_fixtures['super'],
            'admin' => $this->all_fixtures['admin'],
            'user' => $this->all_fixtures['user_1'],
            default => null
        };

        return $user;
    }

    protected function tearDown(): void
    {
        $this->client = null;
    }
}
