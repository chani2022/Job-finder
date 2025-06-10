<?php

namespace App\Tests\src\State\Provider\OffreEmploi;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class OffreEmploiProviderTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testOffreProcess(): void
    {
        $offreEmploiProvider = new OffreEmploiProvider();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
