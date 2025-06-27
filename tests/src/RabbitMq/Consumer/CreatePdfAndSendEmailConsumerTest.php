<?php

namespace App\Tests\src\RabbitMq\Consumer;

use App\Handler\CreatePdfAndSendEmailHandler;
use App\RabbitMq\Consumer\CreatePdfAndSendEmailConsumer;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreatePdfAndSendEmailConsumerTest extends TestCase
{
    private MockObject|CreatePdfAndSendEmailHandler|null $pdfEmailHandler;
    private CreatePdfAndSendEmailConsumer|null $pdfEmailConsumer;

    protected function setUp(): void
    {
        $this->pdfEmailHandler = $this->createMock(CreatePdfAndSendEmailHandler::class);
        $this->pdfEmailConsumer = new CreatePdfAndSendEmailConsumer($this->pdfEmailHandler);
    }

    public function testExecutePdfAndSendEmailSuccess(): void
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

        $amqpMessage = new AMQPMessage(json_encode($data));

        $this->pdfEmailHandler->expects($this->once())
            ->method('handlerPdfAndEmail')
            ->with($data);

        $expected = $this->pdfEmailConsumer->execute($amqpMessage);
        $this->assertTrue($expected);
    }

    public static function getData(): array
    {
        return [
            'title_pdf_email' => [
                [
                    'email' => 'test@test.com',
                    'lettreMotivation' => 'Lettre'
                ]
            ],
            'title_pdf_nom_prenom' => [
                [
                    'nom' => 'nom',
                    'prenom' => 'prenom',
                    'email' => 'test@test.com',
                    'lettreMotivation' => 'Lettre'
                ]
            ]
        ];
    }
    /**
     * @dataProvider keys
     */
    public function testExecutePdfReturnFalse(array $data): void
    {
        $msg = new AMQPMessage(json_encode($data));
        $expected = $this->pdfEmailConsumer->execute($msg);

        $this->assertFalse($expected);
    }

    public static function keys(): array
    {
        return [
            'no_keys' => [[]],
        ];
    }

    protected function tearDown(): void
    {
        $this->pdfEmailHandler = null;
        $this->pdfEmailConsumer = null;
    }
}
