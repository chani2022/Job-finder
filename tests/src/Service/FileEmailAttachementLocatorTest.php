<?php

namespace App\Tests\src\Service;

use App\Service\FileEmailAttachementLocator;
use PHPUnit\Framework\TestCase;

class FileEmailAttachementLocatorTest extends TestCase
{
    private ?string $tmpDir;
    private ?string $filePath;
    private ?FileEmailAttachementLocator $fileEmail;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tmpDir = sys_get_temp_dir();
        $this->filePath = '';
    }
    /**
     * @dataProvider getFileExits
     */
    public function testFileExists(string $filename, bool $exist): void
    {
        $this->filePath = $this->tmpDir . DIRECTORY_SEPARATOR . $filename;
        if ($exist) {
            file_put_contents($this->filePath, 'fake png');
        }

        $this->fileEmail = new FileEmailAttachementLocator($this->tmpDir . DIRECTORY_SEPARATOR);

        $excepted = $this->fileEmail->exists($filename);
        if ($exist) {
            $this->assertTrue($excepted);
            unlink($this->filePath);
        } else {
            $this->assertFalse($excepted);
        }
    }

    public static function getFileExits(): array
    {
        return [
            'file_exists' => ['test.pdf', true],
            'file_not_exists' => ['test.pdf', false]
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->tmpDir = null;
        $this->filePath = null;
    }
}
