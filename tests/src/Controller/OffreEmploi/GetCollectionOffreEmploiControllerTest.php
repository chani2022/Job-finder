<?php

namespace App\Tests\src\Controller\OffreEmploi;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Traits\FixturesTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ApiPlatform\Symfony\Bundle\Test\Response;

class GetCollectionOffreEmploiControllerTest extends ApiTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;

    private ?Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createClient();
    }
    /**
     * @dataProvider getQuery
     */
    public function testGetCollectionOffre(?string $query): void
    {
        $this->client->request('GET', '/api/offre_emplois?query=' . $query);

        $this->assertResponseIsSuccessful();
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

        $this->client = null;
    }
}
