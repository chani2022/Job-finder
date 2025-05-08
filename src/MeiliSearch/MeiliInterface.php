<?php

namespace App\MeiliSearch;

interface MeiliInterface
{
    public function search(?string $query = null): array;
}
