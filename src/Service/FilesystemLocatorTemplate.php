<?php

namespace App\Service;

class FilesystemLocatorTemplate implements FilesystemLocatorTemplateInterface
{
    public function __construct(private readonly string $template_path) {}
    public function exists(string $template): bool
    {
        return file_exists($this->template_path . '' . $template);
    }
}
