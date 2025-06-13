<?php

namespace App\Pdf;

use TCPDF;

class WriterPdf
{
    private ?TCPDF $pdf;

    public function __construct(private readonly string $dir_output_pdf, ?TCPDF $pdf = null)
    {
        $this->pdf = $pdf ?? new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    }

    // public function setTitle(string $title): static {}

    public function setSubject(string $subject): static
    {
        $this->pdf->setSubject($subject);

        return $this;
    }

    public function save(string $filename): void
    {
        $pathfile = $this->dir_output_pdf . DIRECTORY_SEPARATOR . $filename;
        $this->pdf->Output($pathfile, 'F');
    }

    public function getDirOutputPdf(): string
    {
        return $this->dir_output_pdf;
    }
}
