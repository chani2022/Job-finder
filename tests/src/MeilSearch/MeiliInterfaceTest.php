<?php

namespace App\Tests\src\MeilSearch;

use App\MeiliSearch\MeiliInterface;
use PHPUnit\Framework\TestCase;

class MeiliInterfaceTest extends TestCase
{
    public function testSearchWithOutParam(): void
    {
        $meili = $this->createMock(MeiliInterface::class);
        $meili->expects($this->once())
            ->method('search')
            ->willReturn([]);

        $meili->search();
    }

    public function testSearchWithParam(): void
    {
        $meili = $this->createMock(MeiliInterface::class);
        $meili->expects($this->once())
            ->method('search')
            ->with('query')
            ->willReturn([]);

        $meili->search('query');
    }
}
