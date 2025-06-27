<?php

namespace App\Pdf;

use TCPDF;

class WriterPdf
{
    private ?TCPDF $pdf;

    public function __construct(
        private readonly string $dir_output_pdf,
        ?TCPDF $pdf = null

    ) {
        $this->pdf = $pdf ?? new TCPDF();
    }
    /**
     * permet d'ajouter une page
     */
    public function addPage(): static
    {
        $this->pdf->AddPage();

        return $this;
    }
    /**
     * permet d'ajouter une titre
     */
    public function setTitle(string $title): static
    {
        $this->pdf->setTitle($title);

        return $this;
    }
    /**
     * permet d'ajout un sujet
     */
    public function setSubject(string $subject): static
    {
        $this->pdf->setSubject($subject);

        return $this;
    }
    /**
     * permet d'ajout une contenu
     */
    public function setContent(string $content): static
    {
        $this->pdf->Write(h: 10, txt: $content, margin: [10, 10, 10]);

        return $this;
    }
    /**
     * permet de sauvegarder le fichier
     */
    public function save(string $filename): void
    {
        $pathfile = $this->dir_output_pdf . DIRECTORY_SEPARATOR . $filename;
        $this->pdf->Output($pathfile, 'F');
    }
    /**
     * permet de recuperer le chemin ou stocker les pdf
     */
    public function getDirOutputPdf(): string
    {
        return $this->dir_output_pdf;
    }
}
