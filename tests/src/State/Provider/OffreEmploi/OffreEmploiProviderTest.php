<?php

namespace App\Tests\src\State\Provider\OffreEmploi;

use ApiPlatform\Metadata\GetCollection;
use App\Entity\OffreEmploi;
use App\MeiliSearch\MeiliSearchService;
use App\State\OffreEmploi\OffreEmploiProvider;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class OffreEmploiProviderTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    protected function setUp(): void
    {
        parent::setUp();
    }
    /**
     * @dataProvider getQuery
     */
    public function testProvideOffreEmploi(?string $query): void
    {

        $container = static::getContainer();
        $meili_url = $container->getParameter('meilisearch_url');
        $meili_api_key = $container->getParameter('meilisearch_api_key');
        $meili_prefix = $container->getParameter('meilisearch_prefix');

        $meiliSearchService = new MeiliSearchService($meili_url, $meili_api_key, $meili_prefix);

        $operation = new GetCollection();
        $uriVariables = [];
        $context = [
            'request' => new Request(['query' => $query]),
            'resource_class' => OffreEmploi::class
        ];
        $offreEmploiProvider = new OffreEmploiProvider($meiliSearchService);
        $res = $offreEmploiProvider->provide($operation, $uriVariables, $context);

        $this->assertTrue(
            $res['hits'][0]['_formatted']['id']
                >
                $res['hits'][1]['_formatted']['id']
        );

        if ($query) {
            $this->assertStringContainsString('test', $res['hits'][0]['_formatted']['description']);
        } else {
            $this->assertTrue($res['nbHits'] > 5);
        }
        $this->assertMatchesRegularExpression('/(\.\.\.|…)/', $res['hits'][0]['_formatted']['description']);
    }

    public static function getQuery(): array
    {
        return [
            'with_query' => ['titre de test'],
            'without_query' => [null]
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
