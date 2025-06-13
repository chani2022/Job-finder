<?php

namespace App\Service;

class FileEmailAttachementLocator
{
    public function __construct(private readonly string $path_source_image_test) {}

    public function exists(string $name_file): bool
    {
        return file_exists($this->path_source_image_test . DIRECTORY_SEPARATOR . $name_file);
    }

    public function getPathFile(): string
    {
        return $this->path_source_image_test;
    }
}
