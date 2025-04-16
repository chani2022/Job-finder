<?php

namespace App\Tests\src\Mail;

use App\Entity\User;
use App\Mailer\ServiceMailer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerTest extends KernelTestCase
{
    use MailerAssertionsTrait;

    private ?MailerInterface $mailer;
    private ?RequestStack $requestStack;
    private ?Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->mailer = static::getContainer()->get(MailerInterface::class);
        $this->requestStack = static::getContainer()->get(RequestStack::class);
        $this->container = static::getContainer();
    }

    public function testSendMail(): void
    {
        $user = (new User())
            ->setEmail("email@email.com")
            ->setId(200);
        $serviceMailer = new ServiceMailer($this->mailer, $this->requestStack, static::getContainer()->getParameter('domaine_name_server'));
        $serviceMailer->send($user, "Confirmation");
        //verification que l'email est envoyé
        $this->assertEmailCount(1);

        $email = $this->getMailerMessage();
        $this->assertInstanceOf(Email::class, $email);
        $this->assertEmailHeaderSame($email, 'from', $this->container->getParameter('sender_mail'));
        $this->assertEmailHeaderSame($email, 'to', 'email@email.com');
        $this->assertEmailSubjectContains($email, 'Confirmation');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->mailer = null;
        $this->requestStack = null;
    }
}
