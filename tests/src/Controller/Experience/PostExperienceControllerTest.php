<?php

namespace App\Tests\src\Controller\Experience;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\User;
use App\Traits\FixturesTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class PostExperienceControllerTest extends ApiTestCase
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

    public function testUniqueNombreExperience(): void
    {
        $this->myLogUser();

        $this->client->request('POST', '/api/experiences', [
            "json" => [
                'nombre_experience' => '2'
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testNotBlankNombreExperience(): void
    {
        $this->myLogUser();

        $this->client->request('POST', '/api/experiences', [
            "json" => [
                'nombre_experience' => ''
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    /**
     * @dataProvider getUserAuthorized
     */
    public function testPostExperienceAuthorized(?string $roles, bool $access): void
    {
        /** @var User */
        $user = $this->getUser($roles);
        if ($user) {
            $this->client->loginUser($user);
        }
        $this->client->request('POST', '/api/experiences', [
            "json" => [
                "nombre_experience" => "1"
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
