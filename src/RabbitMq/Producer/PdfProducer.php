<?php

namespace App\RabbitMq\Producer;

use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class PdfProducer
{
    public function __construct(private readonly ProducerInterface $generatePdfProducer) {}
    public function publishPdf(array $data)
    {
        $this->generatePdfProducer->publish(serialize($data));
    }
}
