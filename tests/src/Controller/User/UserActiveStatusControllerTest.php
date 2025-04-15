<?php

namespace App\Tests\src\Controller\User;

use App\Traits\FixturesTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;


class UserActiveStatusControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;

    private ?KernelBrowser $client;
    private ?UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);

        $this->loadFixturesTrait();
    }

    public function testActiveUser(): void
    {
        /** @var User $user_to_active */
        $user_to_active = $this->all_fixtures['user_1'];

        $this->client->request("GET", "/user/active/status/" . $user_to_active->getId());

        /** @var User $user_active */
        $user_active = $this->userRepository->find($user_to_active->getId());
        $this->assertTrue($user_active->isStatus());
        $this->assertResponseStatusCodeSame(302);
        // $client->followRedirect();

    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
