<?php

namespace App\Tests\src\Controller\Experience;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\User;
use App\Traits\FixturesTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class GetCollectionAbonnementControllerTest extends ApiTestCase
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
    public function testGetCollectionAbonnement(?string $roles, bool $access): void
    {
        /** @var User */
        $user = $this->getUser($roles);
        if ($user) {
            $this->client->loginUser($user);
        }
        $response = $this->client->request('GET', '/api/abonnements');

        if ($access) {
            $res = $response->toArray();
            $this->assertResponseIsSuccessful();
            if ($roles == 'super') {
                $this->assertCount(11, $res['member']);
            } else if ($roles == 'owner') {
                $this->assertCount(1, $res['member']);
                $this->assertCount(2, $res['member'][0]['category']);
                $this->assertEquals(
                    [
                        "@id" => "/api/users/14",
                        "@type" => "User",
                        "id" => 14,
                        "email" => "adm@user.com",
                        "nom" => null,
                        "prenom" => null,
                        "username" => "adm",
                        "image" => null
                    ],
                    $res['member'][0]['user']
                );
                $this->assertEquals(
                    [
                        [
                            "@id" => "/api/categories/1",
                            "@type" => "Category",
                            "id" => 1,
                            "nom_category" => "unique"
                        ],
                        [
                            "@id" => "/api/categories/2",
                            "@type" => "Category",
                            "id" => 2,
                            "nom_category" => "Et est ut eum nisi."
                        ]
                    ],
                    $res['member'][0]['category']
                );
            } else {
                $this->assertCount(0, $res['member']);
            }
        } else {
            $this->assertResponseStatusCodeSame(401);
        }
    }

    public static function getUserAuthorized(): array
    {
        return [
            'super' => ['roles' => 'super', 'access' => true],
            'user_owner' => ['roles' => 'owner', 'access' => true],
            'admin' => ['roles' => 'admin', 'access' => true],
            'user' => ['roles' => 'user', 'access' => true],
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
            'owner' => $this->all_fixtures['user_adm_society'],
            default => null
        };

        return $user;
    }

    protected function tearDown(): void
    {
        $this->client = null;
    }
}
