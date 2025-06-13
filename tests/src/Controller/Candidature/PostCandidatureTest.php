<?php

namespace App\Tests\src\Controller\Candidature;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Traits\FixturesTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use App\Entity\OffreEmploi;
use App\Entity\User;

class PostCandidatureTest extends ApiTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createClient();
        $this->loadFixturesTrait();
    }

    // public function testPostCandidature(): void
    // {
    // /** @var OffreEmploi */
    // $offreEmploi = $this->all_fixtures['offre_emploi'];
    // $user = $this->all_fixtures['user_adm_society'];

    // $this->client->request('POST', '/api/candidatures', [
    //     'headers' => [
    //         'content-type' => 'multipart/form-data'
    //     ],
    //     'extra' => [
    //         'parameters' => [

    //          // Vos données JSON (nom, prenom, email, username)

    //         'offreEmploi' => '/api/offre_emplois/'.$offreEmploi->getId(),
    //         'candidat' => '/api/users/'.$user->getId(),
    //         'pieceJointe' => [
    //             'lettreMotivation' => 'test de lettre de motivation',
    //             'owner' => '/api/users/'.$user->getId(),
    //             'cv' => 
    //         ]
    //         ]
    //     ]
    // ]);

    // $this->assertResponseStatusCodeSame(401);
    // }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
