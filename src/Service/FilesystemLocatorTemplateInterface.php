<?php

namespace App\Service;

interface FilesystemLocatorTemplateInterface
{
    public function exists(string $template): bool;
}
