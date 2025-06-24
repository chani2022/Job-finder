<?php

namespace App\Tests\src\Mailer;

use App\Mailer\ServiceMailer;
use App\Service\FileEmailAttachementLocator;
use App\Service\FilesystemLocatorTemplate;
use BadMethodCallException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Component\Mailer\MailerInterface;


class ServiceMailerTest extends TestCase
{
    use MailerAssertionsTrait;

    /** @var MockObject|MailerInterface|null */
    private $mailer;
    /** @var MockObject|FilesystemLocatorTemplate|null */
    private $templateLocator;
    private ?ServiceMailer $serviceMailer;
    /** @var MockObject|FileEmailAttachementLocator|null */
    private $fileEmail;
    /** @var MockObject|TemplatedEmail|null */
    private  $templatedEmail;

    private ?string $template_path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template_path = sys_get_temp_dir();
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->templateLocator = new FilesystemLocatorTemplate($this->template_path);
        $this->fileEmail = new FileEmailAttachementLocator($this->template_path);
        $this->templatedEmail = $this->createMock(TemplatedEmail::class);

        $this->serviceMailer = new ServiceMailer(
            $this->mailer,
            $this->fileEmail,
            $this->templateLocator,
            $this->templatedEmail
        );
    }

    public function testTo(): void
    {
        $to = 'test@test.test';
        $this->templatedEmail
            ->expects($this->once())
            ->method('to')
            ->with($to)
            ->willReturnSelf();

        $excepted = [
            'test@test.test'
        ];
        $this->templatedEmail->method('getTo')
            ->willReturn($excepted);

        $this->serviceMailer->to($to);
        $this->assertEquals($excepted, $this->serviceMailer->getTo());
    }

    public function testFrom(): void
    {
        $from = 'test@test.test';
        $this->templatedEmail->expects($this->once())
            ->method('from')
            ->with($from)
            ->willReturnSelf();

        $this->templatedEmail->expects($this->once())
            ->method('getFrom')
            ->willReturn([$from]);

        $this->serviceMailer->from($from);
        $this->assertEquals([$from], $this->serviceMailer->getFrom());
    }

    public function testSubject(): void
    {
        $excepted = 'test de sujet';
        $this->templatedEmail->expects($this->once())
            ->method('subject')
            ->with($excepted)
            ->willReturnSelf();

        $this->templatedEmail->expects($this->once())
            ->method('getSubject')
            ->willReturn($excepted);

        $this->serviceMailer->subject($excepted);

        $this->assertEquals($excepted, $this->serviceMailer->getSubject());
    }

    public function testHtmlTemplate(): void
    {
        $template = 'test.html.twig';

        $this->templatedEmail->expects($this->once())
            ->method('htmlTemplate')
            ->with($template)
            ->willReturnSelf();

        $this->templatedEmail->expects($this->once())
            ->method('getHtmlTemplate')
            ->willReturn($template);

        $this->serviceMailer->htmlTemplate($template);

        $this->assertEquals($template, $this->serviceMailer->getHtmlTemplate());
    }

    public function testAttachFileWithFileExist(): void
    {
        $filename = 'test.pdf';
        $name = 'fake file';
        $pathfile = 'test' . DIRECTORY_SEPARATOR . $filename;

        $this->templatedEmail->expects($this->once())
            ->method('attachFromPath')
            ->with($pathfile, $name)
            ->willReturnSelf();

        $this->templatedEmail->expects($this->once())
            ->method('getAttachments')
            ->willReturn([
                $pathfile
            ]);

        $this->serviceMailer->attachFile($pathfile, $name);

        $this->assertEquals([$pathfile], $this->serviceMailer->getAttachFile());
    }

    public function testContextSuccess(): void
    {
        $context = [
            'user' => 1
        ];

        $this->templatedEmail->expects($this->once())
            ->method('htmlTemplate')
            ->with('test/test.html.twig')
            ->willReturnSelf();

        $this->templatedEmail
            ->method('getHtmlTemplate')
            ->willReturn('test/test.html.twig');

        $this->templatedEmail->expects($this->once())
            ->method('context')
            ->with($context)
            ->willReturnSelf();

        $this->templatedEmail->expects($this->once())
            ->method('getContext')
            ->willReturn($context);

        $this->serviceMailer->htmlTemplate('test/test.html.twig');
        $this->serviceMailer->getHtmlTemplate();
        $this->serviceMailer->getHtmlTemplate();
        $this->serviceMailer->context($context);
        $this->assertEquals($context, $this->serviceMailer->getContext());
    }

    public function testContextFailed(): void
    {
        $context = [
            'user' => 1
        ];

        $this->expectException(BadMethodCallException::class);

        $this->serviceMailer->context($context);
    }



    public function testSend(): void
    {
        $this->templatedEmail->expects($this->once())
            ->method('to')
            ->with('test@test.com')
            ->willReturnSelf();

        $this->templatedEmail->expects($this->once())
            ->method('getTo')
            ->willReturn(['test@test.com']);

        $this->templatedEmail->expects($this->once())
            ->method('from')
            ->with('from@from.test')
            ->willReturnSelf();

        $this->templatedEmail->expects($this->once())
            ->method('getFrom')
            ->willReturn(['from@from.test']);


        $this->mailer->expects($this->once())
            ->method('send')
            ->with($this->templatedEmail);

        $this->serviceMailer
            ->to('test@test.com')
            ->from('from@from.test')
            ->send();

        $this->assertEquals(['test@test.com'], $this->serviceMailer->getTo());
        $this->assertEquals(['from@from.test'], $this->serviceMailer->getFrom());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->serviceMailer = null;
        $this->mailer = null;
        $this->templatedEmail = null;
        $this->fileEmail = null;
        $this->templatedEmail = null;
    }
}
