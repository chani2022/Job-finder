<?php

namespace App\Tests\src\Controller\OffreEmploi;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Traits\FixturesTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

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

    public function testGetCollectionOffre(): void
    {
        $this->client->request('GET', '/api/offre_emplois');

        $this->assertResponseIsSuccessful();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
    }
}
