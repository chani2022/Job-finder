<?php

namespace App\Tests\src\Controller\Experience;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\User;
use App\Traits\FixturesTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class DeleteAbonnementControllerTest extends ApiTestCase
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

    public function testDeleteAbonnement(): void
    {
        $user = $this->all_fixtures['user_adm_society'];
        $this->client->loginUser($user);

        $this->client->request('DELETE', '/api/abonnements/1');

        $this->assertResponseIsSuccessful();
    }

    private function myLogUser(): void
    {
        /** @var User */
        $super = $this->all_fixtures['super'];
        $this->client->loginUser($super);
    }

    protected function tearDown(): void
    {
        $this->client = null;
    }
}
