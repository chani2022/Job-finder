<?php

namespace App\Tests\src\Controller\Experience;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\User;
use App\Traits\FixturesTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class PostAbonnementControllerTest extends ApiTestCase
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

    public function testUniqueNomCategory(): void
    {
        $this->myLogUser();

        $this->client->request('POST', '/api/categories', [
            "json" => [
                'nom_category' => 'unique'
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testNotBlankNomCategory(): void
    {
        $this->myLogUser();

        $this->client->request('POST', '/api/categories', [
            "json" => [
                'nom_category' => ''
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    /**
     * @dataProvider getUserAuthorized
     */
    public function testPostCategoryAuthorized(?string $roles, bool $access): void
    {
        /** @var User */
        $user = $this->getUser($roles);
        if ($user) {
            $this->client->loginUser($user);
        }
        $this->client->request('POST', '/api/categories', [
            "json" => [
                "nom_category" => "symfony"
            ]
        ]);

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

    private function myLogUser(): void
    {
        /** @var User */
        $super = $this->all_fixtures['super'];
        $this->client->loginUser($super);
    }

    protected function tearDown(): void
    {
        $this->client = null;
    }
}
