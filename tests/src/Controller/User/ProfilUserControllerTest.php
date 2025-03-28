<?php

namespace App\Tests\Src\Controller\User;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\User;
use ApiPlatform\Symfony\Bundle\Test\Response;
use App\Traits\FixturesTrait;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class ProfilUserControllerTest extends ApiTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;

    protected ?User $user = null;
    private ?Client $client = null;
    private JWTTokenManagerInterface $jWTTokenManager;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = new User();
        $this->client = static::createClient();
        $this->jWTTokenManager = static::getContainer()->get(JWTTokenManagerInterface::class);
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->loadFixturesTrait();
    }
    /**
     * @dataProvider providePropsErrors
     */
    public function testProfilDataNotValid($data, $excepted): void
    {
        $user_1 = $this->all_fixtures['user_1'];
        $this->client->loginUser($user_1);

        /** @var Response $response */
        $response = $this->client->request("POST", "/api/profil", [
            'json' => $data
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertCount($excepted, $response->getBrowserKitResponse()->toArray()['violations']);
    }

    public function testProfilNeedRoleUser(): void
    {
        $this->client->request("POST", "/api/profil", [
            'json' => [
                "username" => "username",
                "email" => "email@email.com",
                "nom" => "nom",
                "prenom" => "prenom"
            ]
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testProfilOk(): void
    {
        $user_1 = $this->all_fixtures['user_1'];
        $this->client->loginUser($user_1);

        $data = [
            "username" => "username",
            "email" => "email@email.com",
            "nom" => "nom",
            "prenom" => "prenom"
        ];

        /** @var Response $response */
        $response = $this->client->request("POST", "/api/profil", [
            'json' => $data
        ]);

        foreach ($data as $attr => $value) {
            $method = 'set' . ucfirst($attr);
            if (method_exists($user_1, $method)) {
                call_user_func([$user_1, $method], $value);
            }
        }

        $user = $this->em->getRepository(User::class)->find($user_1->getId());

        $this->assertStringContainsString("token", $response->getBrowserKitResponse()->getContent());
        foreach ($data as $attr => $value) {
            $method = 'get' . ucfirst($attr);
            $this->assertEquals($value, call_user_func([$user, $method]));
        }
        // $this->assertResponseStatusCodeSame(401);
    }


    public static function providePropsErrors(): array
    {
        return [
            "nom, prenom, email, username vide" => [
                ["nom" => "", "prenom" => "", "email" => "", "username" => ""],
                4
            ],
            "nom, prenom, email vide" => [
                ["nom" => "", "prenom" => "", "email" => "", "username" => "username"],
                3
            ],
            "nom, prenom vide" => [
                ["nom" => "", "prenom" => "", "email" => "email@email.com", "username" => "username"],
                2
            ],
            "nom vide" => [
                ["nom" => "", "prenom" => "prenom", "email" => "email@email.com", "username" => "username"],
                1
            ],
            "nom et prenom renseigner" => [
                ["nom" => "nom", "prenom" => "prenom", "email" => "", "username" => ""],
                2
            ],
            "nom et email rensegner" => [
                ["nom" => "nom", "prenom" => "", "email" => "email@email.com", "username" => ""],
                2
            ],
            "nom et username rensegner" => [
                ["nom" => "nom", "prenom" => "", "email" => "", "username" => "username"],
                2
            ],
            "prenom et email rensegner" => [
                ["nom" => "", "prenom" => "prenom", "email" => "email@email.com", "username" => ""],
                2
            ],
            "prenom et username rensegner" => [
                ["nom" => "", "prenom" => "prenom", "email" => "", "username" => "username"],
                2
            ],
            "email et username rensegner" => [
                ["nom" => "", "prenom" => "", "email" => "email@email.com", "username" => "username"],
                2
            ],
            "nom renseigner" => [
                ["nom" => "nom", "prenom" => "", "email" => "", "username" => ""],
                3
            ],
            "prenom rensegner" => [
                ["nom" => "", "prenom" => "prenom", "email" => "", "username" => ""],
                3
            ],
            "email rensegner" => [
                ["nom" => "", "prenom" => "", "email" => "email@email.com", "username" => ""],
                3
            ],
            "username rensegner" => [
                ["nom" => "", "prenom" => "", "email" => "", "username" => "username"],
                3
            ],
            "doublons email et username" => [
                ["nom" => "nom", "prenom" => "prenom", "email" => "test@test.com", "username" => "test"],
                2
            ],
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->user = null;
        $this->client = null;
    }
}
