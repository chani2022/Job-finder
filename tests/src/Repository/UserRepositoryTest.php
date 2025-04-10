<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private ?UserRepository $userRepository;

    protected function __setUp(): void
    {
        parent::setUp();
    }
    /**
     * @dataProvider provideUsernameOrEmail
     */
    public function testLoadUserByIdentifier($identifier, $key): void
    {
        $registry = static::getContainer()->get(ManagerRegistry::class);
        $this->userRepository = new UserRepository($registry);

        $user = $this->userRepository->loadUserByIdentifier($identifier);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($identifier, $key == "email" ? $user->getEmail() : $user->getUsername());
    }

    public function testLoadUserWithUsernameOrEmailNotFound(): void
    {
        $registry = static::getContainer()->get(ManagerRegistry::class);
        $this->userRepository = new UserRepository($registry);

        $user = $this->userRepository->loadUserByIdentifier("wrong@wrong.com");

        $this->assertNotInstanceOf(User::class, $user);
    }

    public static function provideUsernameOrEmail(): array
    {
        return [
            "email" => ["test@test.com", "email"],
            "username" => ["test", "username"]
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->userRepository = null;
    }
}
