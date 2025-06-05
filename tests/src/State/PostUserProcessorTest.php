<?php

namespace App\Tests\Src\State;

use ApiPlatform\Metadata\Post;
use App\Entity\User;
use App\Mailer\ServiceMailer;
use App\MeiliSearch\MeiliSearchService;
use App\State\PostUserProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PostUserProcessorTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use MailerAssertionsTrait;

    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $hasher;
    private ?ServiceMailer $serviceMailer;

    public function setUp(): void
    {
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $this->serviceMailer = static::getContainer()->get(ServiceMailer::class);
    }

    public function testCreateUserProcess(): void
    {
        $postUserProcessor = new PostUserProcessor($this->em, $this->hasher, $this->serviceMailer);

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

        //verification que l'email est envoyé
        $this->assertEmailCount(1);

        $email = $this->getMailerMessage();
        $this->assertInstanceOf(Email::class, $email);
        $this->assertEmailHeaderSame($email, 'from', $_ENV['GMAIL_SENDER']);
        $this->assertEmailHeaderSame($email, 'to', $user_bdd->getEmail());
        $this->assertEmailSubjectContains($email, 'Confirmation');

        //verification que l'utilisateur est crée dans meili
        /** @var MeiliSearchService */
        $meili = static::getContainer()->get(MeiliSearchService::class);
        $meili->setIndexName('user');
        $res = $meili->search($user->getUsername());
        $this->assertTrue(count($res['hits']) > 0);
    }
}
