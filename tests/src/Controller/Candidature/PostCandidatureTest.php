<?php

namespace App\Tests\src\Controller\Candidature;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Traits\FixturesTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use App\Entity\OffreEmploi;
use App\Entity\Candidature;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use ApiPlatform\Symfony\Bundle\Test\Response;
use App\Repository\CandidatureRepository;

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

    public function testPostCandidature(): void
    {
        /** @var OffreEmploi */
        $offreEmploi = $this->all_fixtures['offre_emploi'];
        $user = $this->all_fixtures['user_adm_society'];

        $path = $this->simulateFile();

        $this->client->loginUser($user);
        /** @var Response */
        $response = $this->client->request('POST', '/api/postuler/offre', [
            'headers' => [
                'content-type' => 'multipart/form-data'
            ],
            'extra' => [
                'parameters' => [
                    'id_offre' => $offreEmploi->getId(),
                    'lettre' => 'Mon lettre de motivation'
                ],
                'files' => [
                    'file' => new UploadedFile($path, 'cv.pdf', 'application/pdf', null, true),
                ]
            ]
        ]);

        $this->assertResponseIsSuccessful();

        $container = self::getContainer();
        /** @var Candidature */
        $candidature = $container->get(CandidatureRepository::class)->find(1);

        $dir_dest_image = $container->getParameter('path_dest_images_test');
        $path = $dir_dest_image . '' . $candidature->getPieceJointe()->getCv()->filePath;

        $this->assertNotNull($candidature);
        $this->assertFileExists($path);
        // unlink($path);
    }

    private function simulateFile(): string
    {
        $filename = 'cv.pdf';
        $tmp = sys_get_temp_dir();
        $path = $tmp . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($path, '%PDF-1.4 file test');

        return $path;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
