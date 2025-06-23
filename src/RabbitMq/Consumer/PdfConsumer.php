<?php

namespace App\RabbitMq\Consumer;

use App\Pdf\WriterPdf;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class PdfConsumer implements ConsumerInterface
{
    public function __construct(private readonly WriterPdf $writerPdf) {}

    public function execute(AMQPMessage $msg): bool
    {
        //Process picture upload.
        //$msg will be an instance of `PhpAmqpLib\Message\AMQPMessage` with the $msg->body being the data sent over RabbitMQ.
        $bodyArray = unserialize($msg->getBody());

        if (!$bodyArray || !array_key_exists('lettreMotivation', $bodyArray) || !array_key_exists('email', $bodyArray)) {
            return false;
        }

        $filename = $bodyArray['email'] . '.pdf';
        $title = $bodyArray['email'];
        if (array_key_exists('nom', $bodyArray)) {
            $filename = $bodyArray['nom'] . '_' . $bodyArray['prenom'] . '.pdf';
            $title = $bodyArray['nom'] . '_' . $bodyArray['prenom'];
        }

        $this->writerPdf->addPage()
            ->setTitle($title)
            ->setContent($bodyArray['lettreMotivation'])
            ->save($filename);

        return true;
    }
}
