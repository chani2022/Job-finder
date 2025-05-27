<?php

namespace App\Tests\src\Controller\OffreEmploi;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Traits\FixturesTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class GetOffreEmploiControllerTest extends ApiTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;

    private ?Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createClient();
        $this->loadFixturesTrait();
    }

    public function testGetOffre(): void
    {
        $offre_emploi = $this->all_fixtures['offre_emploi'];
        $this->client->request('GET', '/api/offre_emplois/' . $offre_emploi->getId());

        $this->assertResponseIsSuccessful();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
    }
}
