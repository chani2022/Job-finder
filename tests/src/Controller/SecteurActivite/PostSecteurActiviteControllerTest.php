<?php

namespace App\Tests\src\Controller\NiveauEtude;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use App\Entity\User;
use App\Traits\FixturesTrait;

class PostSecteurActiviteControllerTest extends ApiTestCase
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
    public function testPostAuthorizedSecteurActivite(?string $roles, bool $access): void
    {
        /** @var User */
        $user = $this->getUser($roles);
        if ($roles) {
            $this->client->loginUser($user);
        }

        $this->client->request('POST', '/api/secteur_activites', [
            "json" => [
                'type_secteur' => 'informatique'
            ]
        ]);

        if (!$access) {
            $status = 401;
            if ($roles) {
                $status = 403;
            }
            $this->assertResponseStatusCodeSame($status);
        } else {
            $this->assertResponseStatusCodeSame(201);
            $this->assertJsonContains([
                "type_secteur" => "INFORMATIQUE"
            ]);
        }
    }

    /**
     * @dataProvider getDataValid
     */
    public function testPostValidDataSecteurActivite(array $data): void
    {
        $this->myLogUser();

        $this->client->request('POST', '/api/secteur_activites', [
            "json" => $data
        ]);

        if ($data['niveau_etude']) {
            $this->assertResponseStatusCodeSame(201);
        } else {
            $this->assertResponseStatusCodeSame(422);
        }
    }

    public function testUniqueNiveauEtude(): void
    {
        $this->myLogUser();

        $this->client->request('POST', '/api/secteur_activites', [
            "json" => [
                'niveau_etude' => 'unique'
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public static function getDataValid(): array
    {
        return [
            "valid" => [["niveau_etude" => 'bacc']],
            'blank' => [["niveau_etude" => '']]
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

    public static function getUserAuthorized(): array
    {
        return [
            'super' => ['roles' => 'super', 'access' => true],
            'admin' => ['roles' => 'admin', 'access' => false],
            'user' => ['roles' => 'user', 'access' => false],
            'anonymous' => ['roles' => null, 'access' => false]
        ];
    }

    public static function getData(): array
    {
        return [
            'valid' => [['niveau_etude' => 'bacc']],
            'null' => [['niveau_etude' => null]],
            'blank' => [['niveau_etude' => '']],
        ];
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
