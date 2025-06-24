<?php

namespace App\Tests\src\Handler;

use App\Handler\CreatePdfAndSendEmailHandler;
use App\Mailer\ServiceMailer;
use App\Pdf\WriterPdf;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreatePdfAndSendEmailHandlerTest extends TestCase
{
    protected MockObject|WriterPdf|null $writerPdf = null;
    protected MockObject|ServiceMailer|null $serviceMailer = null;
    protected ?CreatePdfAndSendEmailHandler $pdfEmailhandler = null;

    protected function setUp(): void
    {
        $this->writerPdf = $this->createMock(WriterPdf::class);
        $this->serviceMailer = $this->createMock(ServiceMailer::class);
        $this->pdfEmailhandler = new CreatePdfAndSendEmailHandler($this->writerPdf, $this->serviceMailer);
    }

    public function testHandlerPdfAndEmail(): void
    {
        $data = [
            'lettre_motivation_pdf' => [
                'title' => 'titre',
                'content' => 'mon contenu',
                'filename' => 'lettreMotivation.pdf',
                'name' => 'test'
            ],

            'email' => [
                'to' => 'to@to.com',
                'from' => 'from@from.com',
                'cv_filename' => 'cv.pdf',
                'cv_name' => 'test',
                'htmlTemplate' => 'test.html.twig'
            ]
        ];

        $path_lettreMotivation = $this->expectsAndGetFilenameWriterPdf($data);
        $this->expectsSendEmailWithFileAttachment($path_lettreMotivation, $data);

        $this->pdfEmailhandler->handlerPdfAndEmail($data);
    }

    private function expectsAndGetFilenameWriterPdf($data): string
    {
        $this->writerPdf->expects($this->once())
            ->method('addPage')
            ->willReturnSelf();

        $this->writerPdf->expects($this->once())
            ->method('setTitle')
            ->with($data['lettre_motivation_pdf']['title'])
            ->willReturnSelf();

        $this->writerPdf->expects($this->once())
            ->method('setContent')
            ->with($data['lettre_motivation_pdf']['content'])
            ->willReturnSelf();

        $this->writerPdf->expects($this->once())
            ->method('save')
            ->with($data['lettre_motivation_pdf']['filename']);

        $dirOutLettreMotivation = 'test';

        $this->writerPdf->expects($this->once())
            ->method('getDirOutputPdf')
            ->willReturn($dirOutLettreMotivation);

        $path_lettreMotivation = $dirOutLettreMotivation . DIRECTORY_SEPARATOR . $data['lettre_motivation_pdf']['filename'];

        $this->assertEquals('test' . DIRECTORY_SEPARATOR . $data['lettre_motivation_pdf']['filename'], $path_lettreMotivation);

        return $path_lettreMotivation;
    }

    private function expectsSendEmailWithFileAttachment(string $path_lettreMotivation, array $data)
    {

        $this->serviceMailer->expects($this->once())
            ->method('to')
            ->with($data['email']['to'])
            ->willReturnSelf();

        $this->serviceMailer->expects($this->once())
            ->method('from')
            ->with($data['email']['from'])
            ->willReturnSelf();

        $this->serviceMailer->expects($this->once())
            ->method('htmlTemplate')
            ->with($data['email']['htmlTemplate'])
            ->willReturnSelf();

        $this->serviceMailer->expects($this->exactly(2))
            ->method('attachFile')
            ->with($this->callback(function (...$args) use ($path_lettreMotivation, $data) {
                static $call = 0;
                $expected = [
                    [$path_lettreMotivation, null],
                    [$data['email']['cv_filename'], null],
                ];

                [$expectedPath, $expectedName] = $expected[$call];
                [$actualPath, $actualName] = $args;

                $call++;

                return $actualPath === $expectedPath && $actualName === $expectedName;
            }))
            ->willReturnSelf();

        $this->serviceMailer->expects($this->once())
            ->method('send');
    }



    protected function tearDown(): void
    {
        $this->writerPdf = null;
        $this->serviceMailer = null;
        $this->pdfEmailhandler = null;
    }
}
