<?php

namespace App\Tests\src\RabbitMq\Consumer;

use App\Pdf\WriterPdf;
use App\RabbitMq\Consumer\PdfConsumer;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PdfConsumerTest extends TestCase
{
    private MockObject|WriterPdf|null $writerPdf;
    private PdfConsumer|null $pdfConsumer;

    protected function setUp(): void
    {
        $this->writerPdf = new WriterPdf(sys_get_temp_dir());
        $this->pdfConsumer = new PdfConsumer($this->writerPdf);
    }
    /**
     * @dataProvider getData
     */
    public function testExecutePdfSuccess(array $data): void
    {
        $msg = new AMQPMessage(serialize($data));
        $expected = $this->pdfConsumer->execute($msg);

        $dir_output_pdf = $this->writerPdf->getDirOutputPdf();
        $name = $data['nom'] ?
            $data['nom'] . '_' . $data['prenom'] :
            $data['email'];

        $filename = $dir_output_pdf . DIRECTORY_SEPARATOR . $name . '.pdf';

        $this->assertFileExists($filename);
        $this->assertTrue($expected);
        unlink($filename);
    }

    public static function getData(): array
    {
        return [
            'title_pdf_email' => [
                [
                    'email' => 'test@test.com',
                    'lettreMotivation' => 'Lettre'
                ]
            ],
            'title_pdf_nom_prenom' => [
                [
                    'nom' => 'nom',
                    'prenom' => 'prenom',
                    'email' => 'test@test.com',
                    'lettreMotivation' => 'Lettre'
                ]
            ]
        ];
    }
    /**
     * @dataProvider keys
     */
    public function testExecutePdfReturnFalse(array $data): void
    {
        $msg = new AMQPMessage(serialize($data));
        $expected = $this->pdfConsumer->execute($msg);

        $this->assertFalse($expected);
    }

    public static function keys(): array
    {
        return [
            'no_keys' => [[]],
            'email_required' => [
                [
                    'lettreMotivation' => 'lettre',
                ],
            ],
            'lettre_required' => [
                [
                    'email' => 'test@test.test',
                ],
            ],
        ];
    }

    protected function tearDown(): void
    {
        $this->writerPdf = null;
        $this->pdfConsumer = null;
    }
}
