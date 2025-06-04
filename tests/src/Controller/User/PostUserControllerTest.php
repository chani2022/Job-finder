<?php

namespace App\Tests\src\Controller\User;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use ApiPlatform\Symfony\Bundle\Test\Response;
use App\Entity\User;
use App\MeiliSearch\MeiliSearchService;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Traits\FixturesTrait;

class PostUserControllerTest extends ApiTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;

    private ?Client $client = null;
    private ?EntityManagerInterface $em = null;
    private ?UserPasswordHasherInterface $hasher = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient([], [
            'headers' => [
                'content-type' => 'application/json'
            ]
        ]);
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $this->loadFixturesTrait();
    }

    public function testPostUserBlankProps(): void
    {
        /** @var Response $response */
        $response = $this->client->request(Request::METHOD_POST, "/api/users", [
            'json' => [
                "username" => '',
                "email" => '',
                'plainPassword' => '',
                'confirmationPassword' => ''
            ]
        ]);

        $this->assertResponseStatusCodeSame(HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertCount(4, json_decode($response->getBrowserKitResponse()->getContent(), true)['violations']);
    }

    public function testPostUserValid(): void
    {
        $this->client->request(Request::METHOD_POST, "/api/users", [
            'json' => [
                "username" => 'myusername',
                "email" => 'myemail@test.com',
                'plainPassword' => 'test',
                'confirmationPassword' => 'test'
            ]
        ]);

        $this->assertResponseStatusCodeSame(HttpFoundationResponse::HTTP_CREATED);

        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneBy([
            "username" => 'myusername'
        ]);
        $this->assertEquals('myusername', $user->getUsername());
    }
    /**
     * @dataProvider getUniqueProps
     */
    public function testUniquePropsUser(string $username, string $email, string $unique_props): void
    {
        $json = [
            'username' => $username,
            'email' => $email,
            'password' => 'test',
            'confirmationPassword' => 'test'
        ];

        /** @var Response $response */
        $response = $this->client->request('POST', "/api/users", [
            'json' => $json
        ]);

        $this->assertResponseStatusCodeSame(422);
        $excepted = 1;
        if ($unique_props == "username email") {
            $excepted = 2;
        }

        $this->assertCount($excepted, $response->getBrowserKitResponse()->toArray()['violations']);
    }

    public static function getUniqueProps(): array
    {
        return [
            "unique_username" => ["username" => "unique", "email" => "other@gmail.com", "unique_props" => "username"],
            "unique_email" => ["username" => "abcde", "email" => "unique@test.com", "unique_props" => "email"],
            "unique_username_email" => ["username" => "unique", "email" => "unique@test.com", "unique_props" => "username email"]
        ];
    }

    public function testPostCreatePasswordNotMatch(): void
    {

        $response = $this->client->request(Request::METHOD_POST, "/api/users", [
            'json' => [
                "username" => 'username',
                "email" => 'email@test.com',
                'plainPassword' => 'test1',
                'confirmationPassword' => 'test2'
            ]
        ]);

        $this->assertStatusCodeErrorForm();
        $this->assertViolations(1, $response);
    }

    public function assertStatusCodeErrorForm(): void
    {
        $this->assertResponseStatusCodeSame(HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY);
    }
    /**
     * @param int $excepted nombre d'erreur attendue
     */
    public function assertViolations(int $excepted, Response $response): void
    {
        $this->assertCount($excepted, json_decode($response->getBrowserKitResponse()->getContent(), true)['violations']);
    }

    protected function tearDown(): void
    {
        $this->client = null;
        $this->em = null;
        $this->hasher = null;
        parent::tearDown();

        // Réinitialise le kernel entre les tests
        static::ensureKernelShutdown();
    }
}
