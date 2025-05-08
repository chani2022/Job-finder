<?php

namespace App\Tests\src\MeilSearch;

use App\MeiliSearch\MeiliSearchService;
use App\Repository\UserRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\TestCase;
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
        $index_name = 'user';

        $this->meilSearchService = new MeiliSearchService(
            $meili_url,
            $meili_api_key,
            $meili_prefix,
            $index_name
        );
    }

    public function testSearchMeili(): void
    {
        $hits = $this->meilSearchService->search('user', 'test');
        $this->assertCount(count($hits), $hits);
        foreach ($hits as $hit) {
            foreach ($hit as $h) {
                $this->assertStringContainsString("<em>", $h['_formatted']['email']);
            }
        }
    }
}
