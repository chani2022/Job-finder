<?php

namespace App\RabbitMq\Producer;

use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class CreatePdfAndSendEmailProducer
{
    public function __construct(private readonly ProducerInterface $generatePdfSendEmailProducer) {}
    public function publishPdfAndEmail(array $data)
    {
        $this->generatePdfSendEmailProducer->publish(serialize($data));
    }
}
