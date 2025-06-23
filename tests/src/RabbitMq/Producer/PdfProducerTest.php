<?php

namespace App\Tests\src\RabbitMq\Producer;

use App\RabbitMq\Producer\PdfProducer;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PdfProducerTest extends TestCase
{
    private MockObject|ProducerInterface|null $generatePdfproducer;
    private PdfProducer|null $pdfProducer;

    protected function setUp(): void
    {
        $this->generatePdfproducer = $this->createMock(ProducerInterface::class);
        $this->pdfProducer = new PdfProducer($this->generatePdfproducer);
    }

    public function testPublishPdf(): void
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

        $this->pdfProducer->publishPdf($data);
    }

    protected function tearDown(): void
    {
        $this->generatePdfproducer = null;
        $this->pdfProducer = null;
    }
}
