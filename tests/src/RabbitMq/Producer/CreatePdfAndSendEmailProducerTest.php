<?php

namespace App\Tests\src\RabbitMq\Producer;

use App\RabbitMq\Producer\CreatePdfAndSendEmailProducer;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreatePdfAndSendEmailProducerTest extends TestCase
{
    private MockObject|ProducerInterface|null $generatePdfproducer;
    private CreatePdfAndSendEmailProducer|null $pdfEmailProducer;

    protected function setUp(): void
    {
        $this->generatePdfproducer = $this->createMock(ProducerInterface::class);
        $this->pdfEmailProducer = new CreatePdfAndSendEmailProducer($this->generatePdfproducer);
    }

    public function testPublishPdfAndEmail(): void
    {
        $data = [
            'nom' => 'nom',
            'prenom' => 'prenom',
            'email' => 'test@test.com',
            'lettreMotivation' => 'Lettre'
        ];

        $this->generatePdfproducer->expects($this->once())
            ->method('publish')
            ->with(serialize($data));

        $this->pdfEmailProducer->publishPdfAndEmail($data);
    }

    protected function tearDown(): void
    {
        $this->generatePdfproducer = null;
        $this->pdfEmailProducer = null;
    }
}
