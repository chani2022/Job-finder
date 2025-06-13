<?php

namespace App\Tests\src\State\Processor\Candidature;

use ApiPlatform\Metadata\Post;
use App\Entity\Candidature;
use App\Entity\MediaObject;
use App\Entity\PieceJointe;
use App\Repository\CandidatureRepository;
use App\State\Processor\Candidature\CandidatureProcessor;
use App\Traits\FixturesTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Entity\OffreEmploi;
use App\Traits\LogUserTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CandidatureProcessorTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;
    use LogUserTrait;

    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = static::getContainer();
        $this->loadFixturesTrait();
    }

    public function testCandidatureProcessor(): void
    {
        /** @var OffreEmploi */
        $offreEmploi = $this->all_fixtures['offre_emploi'];
        /** @var User */
        $user = $this->all_fixtures['user_adm_society'];

        $this->logUserTrait($user);

        $request = new Request();
        //data
        $request->request->set('lettre', 'une lettre de motivation');
        $request->request->set('id_offre', $offreEmploi->getId());
        //file
        $filename = 'upload.pdf';
        $filepath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($filepath, '%PDF-1.4 fake PDF content');

        /**
         * pour ne pas effacer le fichier dans le dossier fixtures
         * lors de l'instance de uploadedfile
         */
        // copy($path_source_file, $tmp);
        $uploadedFile = new UploadedFile($filepath, $filename, 'application/pdf', null, true);
        $request->files->set('file', $uploadedFile);
        $context['request'] = $request;

        /** @var EntityManagerInterface */
        $em = $this->container->get(EntityManagerInterface::class);
        $validator = $this->container->get(ValidatorInterface::class);
        $tokenStorage = $this->container->get(TokenStorageInterface::class);
        $candidatureProcessor = new CandidatureProcessor($em, $validator, $tokenStorage);

        $res = $candidatureProcessor->process(null, new Post(), [], $context);
        /** @var Candidature */
        $candidature_bdd = $em->getRepository(Candidature::class)->find($res->getId());
        $filename_bdd = $candidature_bdd->getPieceJointe()->getCv()->filePath;

        $this->assertNotNull($res->getId());
        $this->assertEquals($candidature_bdd->getCandidat()->getId(), $user->getId());

        // effacement des fichier uploader
        $path_dest = static::getContainer()->getParameter('path_dest_images_test');
        $pathFilename_dest = $path_dest . DIRECTORY_SEPARATOR . $filename_bdd;
        $this->assertFileExists($pathFilename_dest);
        unlink($pathFilename_dest);
        $this->assertFileDoesNotExist($pathFilename_dest);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
