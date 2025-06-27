<?php

namespace App\Handler;

use App\Mailer\ServiceMailer;
use App\Pdf\WriterPdf;

class CreatePdfAndSendEmailHandler
{
    public function __construct(private WriterPdf $writerPdf, private ServiceMailer $serviceMailer) {}

    public function handlerPdfAndEmail(array $data)
    {
        $this->writerPdf->addPage()
            ->setTitle($data['lettre_motivation_pdf']['title'])
            ->setContent($data['lettre_motivation_pdf']['content'])
            ->save($data['lettre_motivation_pdf']['filename']);

        $path_lettreMotivation = $this->writerPdf->getDirOutputPdf() . DIRECTORY_SEPARATOR . $data['lettre_motivation_pdf']['filename'];
        $path_cv = $this->serviceMailer->getDirFileEmailLocator() . '' . $data['email']['cv_filename'];

        $this->serviceMailer->to($data['email']['to'])
            ->from($data['email']['from'])
            ->htmlTemplate($data['email']['htmlTemplate'])
            ->attachFile($path_lettreMotivation)
            ->attachFile($path_cv)
            ->send();
    }
}
