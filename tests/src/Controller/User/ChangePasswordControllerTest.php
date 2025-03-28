<?php

namespace App\Tests\src\Controller\User;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use ApiPlatform\Symfony\Bundle\Test\Response;
use App\Traits\FixturesTrait;

class ChangePasswordControllerTest extends ApiTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;

    private ?EntityManagerInterface $em = null;
    private ?UserPasswordHasherInterface $hasher = null;
    private ?Client $client = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient([], [
            'headers' => [
                'Content-Type' => 'application/json',
                'accept' => 'application/ld+json'
            ],
        ]);

        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $this->loadFixturesTrait();
    }

    public function testPutChangePasswordUserNotAuth(): void
    {
        $this->client->request("PUT", "/api/change-password", [
            "json" => [
                "password" => "test",
                "newPassword" => "motdepasse",
                "confirmationPassword" => "motdepasse"
            ]
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testChangePasswordValid(): void
    {
        /** @var User $user_1 */
        $user_1 = $this->all_fixtures['user_1'];

        $this->client->loginUser($user_1);
        /** @var Response $response */
        $response = $this->client->request("PUT", "/api/change-password", [
            'json' => [
                "password" => "test",
                "newPassword" => "motdepasse",
                "confirmationPassword" => "motdepasse"
            ]
        ]);

        $user_1 = $this->em->getRepository(User::class)->find($user_1->getId());
        $this->assertTrue($this->hasher->isPasswordValid($user_1, "motdepasse"));
        $this->assertStringContainsString('token', $response->getBrowserKitResponse()->getContent());
    }

    /**
     * @dataProvider provideInvalidPutChangePassword
     */
    public function testPutChangePasswordValidationErrors(array $userData, int $exceptedErrors): void
    {

        /** @var User $user_1 */
        $user_1 = $this->all_fixtures['user_1'];

        $this->client->loginUser($user_1);
        /** @var Response $response */
        $response = $this->client->request("PUT", "/api/change-password", [
            'json' => $userData
        ]);

        $errors = $response->getBrowserKitResponse()->toArray()['violations'];
        $this->assertResponseStatusCodeSame(422);
        $this->assertCount($exceptedErrors, $errors);
    }

    public static function provideInvalidPutChangePassword(): array
    {
        return [
            "blank and current password wrong" => [
                ["password" => "", "newPassword" => "", "confirmationPassword" => ""],
                4
            ],
            "current password wrong" => [
                ["password" => "wrongPassword", "newPassword" => "test", "confirmationPassword" => "test"],
                1
            ],
            "current password wrong and 2 password not match" => [
                ["password" => "wrongPassword", "newPassword" => "test", "confirmationPassword" => "confirm"],
                2
            ],
            "2 password not match" => [
                ["password" => "test", "newPassword" => "test", "confirmationPassword" => "confirm"],
                1
            ]
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em = null;
        $this->client = null;
        $this->hasher = null;
        // Réinitialise le kernel entre les tests
        static::ensureKernelShutdown();
    }
}
