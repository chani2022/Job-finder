<?php

namespace App\Tests\State;

use App\Entity\User;
use App\State\ChangePasswordProcessor;
use ApiPlatform\Metadata\Post;
use App\Traits\FixturesTrait;
use App\Traits\LogUserTrait;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ChangePasswordProcessorTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;
    use LogUserTrait;

    private ?Security $security;
    private ?UserPasswordHasherInterface $hasher;
    private ?JWTTokenManagerInterface $jwtManager;
    private ?EntityManagerInterface $em;
    private ?ChangePasswordProcessor $changePasswordProcessor;

    protected function setUp(): void
    {
        $this->security = static::getContainer()->get(Security::class);
        $this->hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $this->jwtManager = static::getContainer()->get(JWTTokenManagerInterface::class);
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $this->changePasswordProcessor = new ChangePasswordProcessor(
            $this->security,
            $this->hasher,
            $this->jwtManager,
            $this->em
        );

        $this->loadFixturesTrait();
    }

    public function testProcessChangePassword()
    {
        $data = (new User())
            ->setConfirmationPassword("confirm");

        $auth = $this->all_fixtures['user_1'];

        $this->logUserTrait($auth);

        /** @var JsonResponse $response */
        $response = $this->changePasswordProcessor->process($data, new Post(), [], []);

        $auth = $this->em->getRepository(User::class)->find($auth);

        $this->assertTrue($this->hasher->isPasswordValid($auth, 'confirm'));
        $this->assertStringContainsString("token", $response->getContent());
    }
}
