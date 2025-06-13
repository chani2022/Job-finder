<?php

namespace App\Tests\src\Pdf;

use App\Pdf\WriterPdf;
use PHPUnit\Framework\TestCase;
use TCPDF;

class WriterPdfTest extends TestCase
{
    private ?WriterPdf $writer;
    private ?string $tmpDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tmpDir = sys_get_temp_dir();
        $this->writer = new WriterPdf($this->tmpDir);
    }

    public function testGetDirOutPdf(): void
    {
        $output = $this->writer->getDirOutputPdf();
        $this->assertEquals($this->tmpDir, $output);
    }

    public function testSetSubject(): void
    {
        $subject = 'test de subject';
        $filename = 'test.pdf';
        $this->writer
            ->setSubject($subject)
            ->save($filename);

        $content = file_get_contents($this->tmpDir . DIRECTORY_SEPARATOR . $filename);
        $this->assertStringContainsString('/Subject (' . $subject . ')', $content);
        unlink($this->tmpDir . DIRECTORY_SEPARATOR . $filename);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->writer = null;
    }
}
