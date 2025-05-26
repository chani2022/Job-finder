<?php

namespace App\Tests\src\Controller\OffreEmploi;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\User;
use App\Traits\FixturesTrait;
use DateTime;
use DateTimeImmutable;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class PostOffreEmploiControllerTest extends ApiTestCase
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
    public function testPostOffre(?string $roles, bool $access, array $data): void
    {
        $user = $this->getUser($roles);
        if ($user) {
            $this->client->loginUser($user);
            $data['titre'] .= $user->getId();
            $data['description'] .= $user->getId();
        }

        $this->client->request('POST', '/api/offre_emplois', [
            "json" => $data
        ]);
        if ($access) {
            $this->assertResponseStatusCodeSame(201);
        } else {
            $this->assertResponseStatusCodeSame(401);
        }
    }

    /**
     * @dataProvider getDataInvalid
     */
    public function testPostOffreInvalid(array $data): void
    {
        $admin = $this->all_fixtures['admin'];
        $this->client->loginUser($admin);

        $this->client->request('POST', '/api/offre_emplois', [
            'json' => $data
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public static function getDataInvalid(): array
    {
        return [
            'data' => [
                [
                    "titre" => "",
                    "description" => "",
                    "date_expired_at" => '2025-06-22',
                    "typeContrat" => '/api/type_contrats/1',
                    'secteurActivite' => '/api/secteur_activites/1',
                    'niveauEtude' => '/api/niveau_etudes/1',
                    'experience' => '/api/experiences/1'
                ]
            ]
        ];
    }

    public static function getUserAuthorized(): array
    {
        return [
            'super' => ['roles' => 'super', 'access' => true, 'data' => [
                "titre" => "mon titre ",
                "description" => "mon description ",
                "date_expired_at" => '2025-06-22',
                "typeContrat" => '/api/type_contrats/1',
                'secteurActivite' => '/api/secteur_activites/1',
                'niveauEtude' => '/api/niveau_etudes/1',
                'experience' => '/api/experiences/1'
            ]],
            'admin' => ['roles' => 'admin', 'access' => true, 'data' => [
                "titre" => "mon titre ",
                "description" => "mon description "
            ]],
            'user' => ['roles' => 'user', 'access' => false, 'data' => [
                "titre" => "mon titre ",
                "description" => "mon description ",
                "date_expired_at" => '2025-06-22',
                "typeContrat" => '/api/type_contrats/1',
                'secteurActivite' => '/api/secteur_activites/1',
                'niveauEtude' => '/api/niveau_etudes/1',
                'experience' => '/api/experiences/1'
            ]],
            'anonymous' => ['roles' => null, 'access' => false, 'data' => [
                "titre" => "mon titre ",
                "description" => "mon description ",
                "date_expired_at" => '2025-06-22',
                "typeContrat" => '/api/type_contrats/1',
                'secteurActivite' => '/api/secteur_activites/1',
                'niveauEtude' => '/api/niveau_etudes/1',
                'experience' => '/api/experiences/1'
            ]]
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
        parent::tearDown();

        $this->client = null;
    }
}
