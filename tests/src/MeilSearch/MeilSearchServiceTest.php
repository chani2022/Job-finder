<?php

namespace App\Tests\src\MeilSearch;

use App\MeiliSearch\MeiliSearchService;
use Error;
use Exception;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Meilisearch\Exceptions\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;

class MeilSearchServiceTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    protected ?Container $container;
    protected ?MeiliSearchService $meilSearchService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = static::getContainer();
        $meili_url = $this->container->getParameter('meilisearch_url');
        $meili_api_key = $this->container->getParameter('meilisearch_api_key');
        $meili_prefix = $this->container->getParameter('meilisearch_prefix');

        $this->meilSearchService = new MeiliSearchService(
            $meili_url,
            $meili_api_key,
            $meili_prefix,
        );
    }
    /**
     * @dataProvider getIndexForSearch
     */
    public function testSearchMeili(?string $index_name): void
    {
        if ($index_name) {
            $this->meilSearchService->setIndexName('user');
        } else {
            $this->expectException(Exception::class);
        }
        $hits = $this->meilSearchService->search('test');
        $this->assertCount(count($hits), $hits);
        foreach ($hits as $hit) {
            foreach ($hit as $h) {
                $this->assertStringContainsString("<em>", $h['_formatted']['email']);
            }
        }
    }

    public function testGetIndexNames(): void
    {
        $indexes = $this->meilSearchService->getIndexNames();

        $this->assertTrue(count($indexes) > 0);
    }
    /**
     * @dataProvider listIndexName
     */
    public function testSetIndexName(bool $is_valid, string $index_name): void
    {
        if (!$is_valid) {
            $this->expectException(InvalidArgumentException::class);
        }
        $this->meilSearchService->setIndexName($index_name);
        if ($is_valid) {
            $this->assertEquals($index_name, $this->meilSearchService->getIndexName());
        }
    }

    public static function listIndexName(): array
    {
        return [
            'invalid' => ['is_valid' => false, 'index_name' => 'other'],
            'user_index_valid' => ['is_valid' => true, 'index_name' => 'user'],
            'society_index_valid' => ['is_valid' => true, 'index_name' => 'society'],
            'offre_emploi_index_valid' => ['is_valid' => true, 'index_name' => 'offre_emploi'],

        ];
    }

    public static function getIndexForSearch(): array
    {
        return [
            'with_index' => ['index_name' => 'user'],
            'without_index' => ['index_name' => null]
        ];
    }
}
