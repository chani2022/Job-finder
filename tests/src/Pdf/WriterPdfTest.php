<?php

namespace App\Tests\src\Pdf;

use App\Pdf\WriterPdf;
use PHPUnit\Framework\TestCase;
use Smalot\PdfParser\Parser;


class WriterPdfTest extends TestCase
{
    private ?WriterPdf $writer;
    private ?string $tmpDir;
    private ?Parser $parser;
    private ?string $filename;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tmpDir = sys_get_temp_dir();
        $this->writer = new WriterPdf($this->tmpDir);
        $this->parser = new Parser();
        $this->filename = 'test.pdf';
    }

    public function testAddPage(): void
    {
        $this->writer->addPage()
            ->save($this->filename);

        $pdf = $this->parser->parseFile($this->tmpDir . DIRECTORY_SEPARATOR . $this->filename);
        $this->assertNotNull($pdf->getPages()[0]);
    }

    public function testTitle(): void
    {
        $title = 'mon titre de pdf';
        $this->writer->setTitle($title)
            ->save($this->filename);

        $details = $this->getDetails();
        $this->assertAndDelFile($title, $details['Title']);
    }

    public function testSetSubject(): void
    {
        $subject = 'test de subject';

        $this->writer
            ->setSubject($subject)
            ->save($this->filename);

        $details = $this->getDetails();
        $this->assertAndDelFile($subject, $details['Subject']);
    }

    public function testContent(): void
    {
        $content = 'Mon contenu de test';
        $this->writer
            ->addPage()
            ->setContent($content)
            ->save($this->filename);

        $pdf = $this->parser->parseFile($this->tmpDir . DIRECTORY_SEPARATOR . $this->filename);
        $text = $pdf->getText();
        $this->assertStringContainsString($content, $text);
        unlink($this->tmpDir . DIRECTORY_SEPARATOR . $this->filename);
    }

    public function testGetDirOutPdf(): void
    {
        $output = $this->writer->getDirOutputPdf();
        $this->assertEquals($this->tmpDir, $output);
    }

    private function getDetails(): array
    {
        $pdf = $this->parser->parseFile($this->tmpDir . DIRECTORY_SEPARATOR . $this->filename);
        return $pdf->getDetails();
    }

    private function assertAndDelFile(string $excepted, string $actual): void
    {
        $this->assertEquals($excepted, $actual);
        unlink($this->tmpDir . DIRECTORY_SEPARATOR . $this->filename);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->writer = null;
    }
}
