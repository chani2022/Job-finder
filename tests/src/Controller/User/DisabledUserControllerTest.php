<?php

namespace App\Tests\src\Controller\User;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\User;
use App\Traits\FixturesTrait;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class DisabledUserControllerTest extends ApiTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;

    private ?Client $client = null;
    private ?EntityManagerInterface $em = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient([], [
            "headers" => [
                "content-type" => "application/json",
            ]
        ]);
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->loadFixturesTrait();
    }

    public function testDisabledUser(): void
    {
        $admin = $this->all_fixtures['admin'];
        /** @var User $user_to_disabled */
        $user_to_disabled = $this->all_fixtures['user_1'];

        $this->client->loginUser($admin);

        $response = $this->client->request('PUT', '/api/disabled/' . $user_to_disabled->getId(), [
            "json" => []
        ]);

        /** @var User $user_modify */
        $user_modify = $this->em->getRepository(User::class)->find($user_to_disabled->getId());

        $this->assertNotTrue($user_modify->isStatus());
        $this->assertResponseStatusCodeSame(200);
    }
    /**
     * @dataProvider provideUserNotAllowed
     */
    public function testUserNotModifyStatus(bool $user_defined, int $status): void
    {
        if ($user_defined) {
            $user = $this->all_fixtures['user_1'];
            $this->client->loginUser($user);
        }

        $user_to_disabled = $this->all_fixtures['user_1'];

        $this->client->request('PUT', '/api/disabled/' . $user_to_disabled->getId(), [
            "json" => []
        ]);

        $this->assertResponseStatusCodeSame($status);
    }

    public static function provideUserNotAllowed(): array
    {

        return [
            [false, 401],
            [true, 403]
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
    }
}
