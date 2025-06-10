<?php

namespace App\Tests\src\State\Provider\OffreEmploi;

use ApiPlatform\Metadata\GetCollection;
use App\Entity\OffreEmploi;
use App\MeiliSearch\MeiliSearchService;
use App\State\OffreEmploi\OffreEmploiProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class OffreEmploiProviderTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testOffreProvide(): void
    {
        $meiliSearchService = $this->createMock(MeiliSearchService::class);
        $operation = new GetCollection();
        $uriVariables = [];
        $context = [
            'request' => new Request(["query" => "test"]),
            'resource_class' => OffreEmploi::class
        ];
        $offreEmploiProvider = new OffreEmploiProvider($meiliSearchService);
        $res = $offreEmploiProvider->provide($operation, $uriVariables, $context);

        $this->assertTrue($res['hits'] > 0);
        $this->assertTrue($res['hits'][0]['_formatted']['id'], $res['hits'][1]['_formatted']['id']);
        $this->assertStringContainsString('test', $res['hits'][0]['_formatted']['description']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
