<?php

namespace App\RabbitMq\Consumer;

use App\Handler\CreatePdfAndSendEmailHandler;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class CreatePdfAndSendEmailConsumer implements ConsumerInterface
{
    public function __construct(private CreatePdfAndSendEmailHandler $pdfEmailHandler) {}

    public function execute(AMQPMessage $msg): bool
    {
        $bodyArray = json_decode($msg->getBody(), true);
        if (!$bodyArray) {
            return false;
        }
        $this->pdfEmailHandler->handlerPdfAndEmail($bodyArray);

        return true;
    }
}
