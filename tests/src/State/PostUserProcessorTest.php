<?php

namespace App\Tests\Src\State;

use ApiPlatform\Metadata\Post;
use App\Entity\User;
use App\State\PostUserProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PostUserProcessorTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $hasher;

    public function setUp(): void
    {
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
    }

    public function testCreateUserProcess(): void
    {
        $postUserProcessor = new PostUserProcessor($this->em, $this->hasher);

        $user = (new User())
            ->setEmail("email@email.com")
            ->setUsername("username")
            ->setPlainPassword("password")
            ->setConfirmationPassword("password");

        $post = new Post();
        $user_process = $postUserProcessor->process($user, $post);

        $user_bdd = $this->em->getRepository(User::class)->find($user_process->getId());

        $this->assertTrue($this->hasher->isPasswordValid($user, "password"));
        $this->assertStringContainsString("$", $user_bdd->getPassword());
        $this->assertEquals($user->getEmail(), $user_process->getEmail());

        $this->assertNotNull($user_bdd);
    }
}
