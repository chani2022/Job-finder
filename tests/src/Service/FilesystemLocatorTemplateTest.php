<?php

namespace App\Tests\src\Service;

use App\Service\FilesystemLocatorTemplate;
use PHPUnit\Framework\TestCase;

class FilesystemLocatorTemplateTest extends TestCase
{
    /**
     * @dataProvider getFilename
     */
    public function testFilesystemLocatorTemplateExists(string $templateName, $exist): void
    {
        $tmp = sys_get_temp_dir();
        $filePath = $tmp . DIRECTORY_SEPARATOR . $templateName;
        if ($exist) {
            file_put_contents($filePath, 'fake template');
        }

        $fileLocatorTemplate = new FilesystemLocatorTemplate($tmp . DIRECTORY_SEPARATOR);
        $excepted = $fileLocatorTemplate->exists($templateName);

        if ($excepted) {
            $this->assertTrue($excepted);
            unlink($filePath);
        } else {
            $this->assertFalse($excepted);
        }
    }

    public static function getFilename(): array
    {
        return [
            'template_exist' => ['test/test.html.twig', true],
            'template_not_exist' => ['test/test.html.twig', false]
        ];
    }
}
