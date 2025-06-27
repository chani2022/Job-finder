<?php

namespace App\Tests\src\State\Processor\Candidature;

use ApiPlatform\Metadata\Post;
use App\Entity\Candidature;
use App\State\Processor\Candidature\CandidatureProcessor;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Entity\OffreEmploi;
use App\RabbitMq\Producer\CreatePdfAndSendEmailProducer;
use App\Repository\OffreEmploiRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserProvider;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CandidatureProcessorTest extends TestCase
{

    /** @var MockObject|EntityManagerInterface*/
    private $em = null;
    /** @var MockObject|ValidatorInterface */
    private $validator;
    private ?TokenStorage $tokenStorage = null;
    private ?CandidatureProcessor $candidatureProcessor = null;
    /** @var UserProviderInterface|null|MockObject */
    private $userProvider = null;
    private MockObject|CreatePdfAndSendEmailProducer|null $pdfEmailProducer = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->tokenStorage = new TokenStorage();
        $this->userProvider = new JWTUserProvider(User::class);
        $this->pdfEmailProducer = $this->createMock(CreatePdfAndSendEmailProducer::class);


        $this->candidatureProcessor = new CandidatureProcessor(
            $this->em,
            $this->validator,
            $this->tokenStorage,
            $this->userProvider,
            $this->pdfEmailProducer
        );
    }

    /**
     * Teste le traitement complet d’une candidature valide : 
     * - Création
     * - Validation
     * - Sauvegarde
     * - Génération PDF et Envoi par email via le Producer rabbitmq
     */
    public function testProcessCreateCandidatureAndGeneratePdfAndSendEmail(): void
    {
        $operation = new Post();
        $data = [];
        $context = [];

        $user = (new User())
            ->setEmail('test@test.com')
            ->setId(1);

        $token = new UsernamePasswordToken($user, 'jwt');
        $this->tokenStorage->setToken($token);

        $userRepository = $this->createMock(UserRepository::class);
        $offreEmploiRepository = $this->createMock(OffreEmploiRepository::class);
        $this->em
            ->method('getRepository')
            ->willReturnMap([
                [User::class, $userRepository],
                [OffreEmploi::class, $offreEmploiRepository]
            ]);

        $userRepository->expects($this->once())
            ->method('find')
            ->with($user->getId())
            ->willReturn($user);

        $dataRequest = [
            'id_offre' => 1,
            'lettre' => 1
        ];
        $dataFile = $this->createFileAttachmentsForEmail($user);

        $context['request'] = new Request([], $dataRequest, [], [], $dataFile['uploadedFile']);

        $lettre = $context['request']->request->get('lettre');
        $id_offreEmploi = $context['request']->request->get('id_offre');

        $offreEmploi = $this->validateCandidature($offreEmploiRepository, $id_offreEmploi);

        $this->saveCandidature();
        $excepted = [];

        $this->assertCreatePdfAndSendEmailProducerCalled($excepted);

        //appel à la methode à tester
        $candidatureActual = $this->candidatureProcessor->process($data, $operation, [], $context);

        $this->assertInstanceOf(Candidature::class, $candidatureActual);
        $this->assertEquals($offreEmploi, $candidatureActual->getOffreEmploi());
        $this->assertEquals($lettre, $candidatureActual->getPieceJointe()->getLettreMotivation());
        $this->assertEquals($dataFile['uploadedFile']['file'], $candidatureActual->getPieceJointe()->getCv()->file);
        $this->assertEquals($user, $candidatureActual->getCandidat());
        $this->assertEquals($user, $candidatureActual->getPieceJointe()->getOwner());

        $this->assertEquals($excepted, $this->candidatureProcessor->getDataToCreatePdfAndSendEmail($candidatureActual, $lettre));
    }

    private function assertCreatePdfAndSendEmailProducerCalled(array &$expected)
    {
        $this->pdfEmailProducer
            ->expects($this->once())
            ->method('publishPdfAndEmail')
            ->with($this->callback(function ($actualData) use (&$expected) {
                $expected = $actualData;
                return true;
            }));
    }

    private function createFileAttachmentsForEmail(User $user): array
    {
        $filename = 'test.pdf';
        $name = $user->getEmail();
        $pathfile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($pathfile, 'fake file');
        $file = new UploadedFile($pathfile, $filename, 'application/pdf', null, true);
        return [
            'uploadedFile' => ['file' => $file],
            'filename' => $filename,
            'name' => $name,
            'pathfile' => $pathfile
        ];
    }

    private function validateCandidature(OffreEmploiRepository $offreEmploiRepository, $id_offreEmploi): OffreEmploi
    {
        $offreEmploi = (new OffreEmploi())
            ->setUser(
                (new User())
                    ->setEmail('offre@offre.com')
            );
        $offreEmploiRepository
            ->method('find')
            ->with($id_offreEmploi)
            ->willReturn($offreEmploi);

        $constraintViolationList = $this->createMock(ConstraintViolationList::class);
        $constraintViolationList->method('count')->willReturn(0);
        $this->validator->expects($this->once())
            ->method('validate')
            ->with(
                $this->callback(fn($value) => $value instanceof Candidature),
                null,
                ['groups' => 'post:validator']
            )
            ->willReturn($constraintViolationList);

        return $offreEmploi;
    }

    private function saveCandidature(): void
    {
        $this->em->expects($this->once())
            ->method('persist')
            ->with($this->callback(fn($value) => $value instanceof Candidature));

        $this->em->expects($this->once())
            ->method('flush');
    }

    public function testGetAuthenticatedUserSuccess(): void
    {
        $user = (new User())
            ->setId(1);
        $token = new UsernamePasswordToken($user, 'jwt');
        $this->tokenStorage->setToken($token);

        $userRepository = $this->createMock(UserRepository::class);
        $this->em->expects($this->once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($userRepository);

        $userRepository->expects($this->once())
            ->method('find')
            ->with($user->getId())
            ->willReturn($user);

        $res = $this->candidatureProcessor->getAuthenticatedUser();

        $this->assertSame($res, $user);
    }

    public function testGetAuthenticatedUserThrowException(): void
    {
        $this->expectException(LogicException::class);

        $this->candidatureProcessor->getAuthenticatedUser();
    }

    public function testCheckKeyRequestFromContextSuccess(): void
    {
        $context['request'] = new Request();
        $requestIn = false;
        if (array_key_exists('request', $context)) {
            $requestIn = true;
        }
        $this->candidatureProcessor->checkKeyRequestFromContext($context);
        $this->assertTrue($requestIn);
    }

    public function testCheckKeyRequestFromContextThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->candidatureProcessor->checkKeyRequestFromContext([]);
    }

    public function testCheckKeysInRequestSuccess(): void
    {
        $request = new Request([], [
            'id_offre' => '',
            'lettre' => ''
        ], [], [], [
            'file' => $this->createMock(UploadedFile::class)
        ]);

        $res = $this->candidatureProcessor->checkKeysRequest($request);
        $this->assertTrue($res);
    }
    /**
     * @dataProvider getDataInvalid
     */
    public function testCheckKeysThrowException(array $data): void
    {
        $this->expectException(InvalidArgumentException::class);
        $request = new Request([], $data, [], [
            'file' => $this->createMock(UploadedFile::class)
        ], []);

        $this->candidatureProcessor->checkKeysRequest($request);
    }

    public static function getDataInvalid(): array
    {
        return [
            'data lettre missing ' => [
                [
                    'id_offre' => 1,
                ]
            ],
            'data id_offre missing' => [
                [
                    'lettre' => 'lettre'
                ]
            ],
        ];
    }
    public function testValidatorCandidatureNotException(): void
    {
        $candidature = new Candidature();
        $violationsList = $this->createMock(ConstraintViolationList::class);
        $violationsList->expects($this->once())
            ->method('count')
            ->willReturn(0);
        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn($violationsList);

        $this->candidatureProcessor->validate($candidature);
    }

    public function testGetDataRequestSuccess(): void
    {
        $request = new Request([], [
            'id_offre' => 1,
            'lettre' => 'lettre'
        ], [], [], [
            'file' => $this->createMock(UploadedFile::class)
        ]);

        $res = $this->candidatureProcessor->getDataRequest($request);

        $this->assertEquals(1, $res['id_offre']);
        $this->assertEquals('lettre', $res['lettre']);
        $this->assertInstanceOf(UploadedFile::class, $res['file']);
    }
    /**
     * @dataProvider getDataRequestInvalid
     */
    public function testGetDataRequestThrowException(array $data): void
    {
        $request = new Request([], $data, [], [], [
            'file' => $this->createMock(UploadedFile::class)
        ]);

        $this->expectException(InvalidArgumentException::class);

        $this->candidatureProcessor->getDataRequest($request);
    }

    public static function getDataRequestInvalid(): array
    {
        return [
            'id_offreEmploi null' => [
                [
                    'id_offre' => null,
                    'lettre' => 'lettre',
                ]
            ],
            'lettre missing' => [
                [
                    'id_offre' => 1,
                    'lettre' => null,
                ]
            ],
            'lettre et id_offre missing' => [
                [
                    'id_offre' => null,
                    'lettre' => null,
                ]
            ]
        ];
    }


    public function testValidatorCandidatureThrowException(): void
    {
        $candidature = new Candidature();
        $violationList = $this->createMock(ConstraintViolationList::class);
        $violationList->expects($this->once())
            ->method('count')
            ->willReturn(1);
        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn($violationList);

        $this->expectException(ValidatorException::class);

        $this->candidatureProcessor->validate($candidature);
    }

    public function testSetCandidatureSuccess(): void
    {
        $user = new User();
        $id_offreEmploi = 1;
        $lettre = 'lettre';
        $file = $this->createMock(UploadedFile::class);
        $offreEmploi = new OffreEmploi();

        $offreEmploiRepository = $this->createMock(OffreEmploiRepository::class);

        $this->em->expects($this->once())
            ->method('getRepository')
            ->with(OffreEmploi::class)
            ->willReturn($offreEmploiRepository);

        $offreEmploiRepository->expects($this->once())
            ->method('find')
            ->with($id_offreEmploi)
            ->willReturn($offreEmploi);

        $candidature = $this->candidatureProcessor->setCandidature($user, $id_offreEmploi, $lettre, $file);

        $this->assertEquals($user, $candidature->getCandidat());
        $this->assertEquals($offreEmploi, $candidature->getOffreEmploi());
        $this->assertEquals($lettre, $candidature->getPieceJointe()->getLettreMotivation());
        $this->assertEquals($user, $candidature->getPieceJointe()->getOwner());
        $this->assertEquals($file, $candidature->getPieceJointe()->getCv()->file);
        $this->assertEquals($file->getClientOriginalName(), $candidature->getPieceJointe()->getCv()->filePath);
    }

    public function testSetCandidatureThrowException(): void
    {
        $user = new User();
        $id_offreEmploi = 1;
        $lettre = 'lettre';
        $file = $this->createMock(UploadedFile::class);

        $offreEmploiRepository = $this->createMock(OffreEmploiRepository::class);
        $this->em->expects($this->once())
            ->method('getRepository')
            ->with(OffreEmploi::class)
            ->willReturn($offreEmploiRepository);

        $offreEmploiRepository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->candidatureProcessor->setCandidature($user, $id_offreEmploi, $lettre, $file);
    }

    public function testSave(): void
    {
        $candidature = new Candidature();
        $this->em->expects($this->once())
            ->method('persist')
            ->with($this->callback(fn($excepted) => $excepted instanceof Candidature));

        $this->em->expects($this->once())
            ->method('flush');

        $this->candidatureProcessor->save($candidature);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em = null;
        $this->validator = null;
        $this->tokenStorage = null;
        $this->candidatureProcessor = null;
        $this->pdfEmailProducer = null;
    }
}
