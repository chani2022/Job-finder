<?php

namespace App\Tests\src\State\Processor\Candidature;

use ApiPlatform\Metadata\Post;
use App\Entity\Candidature;
use App\State\Processor\Candidature\CandidatureProcessor;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Entity\OffreEmploi;
use App\Repository\OffreEmploiRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->tokenStorage = new TokenStorage();
        $this->userProvider = $this->createMock(UserProviderInterface::class);

        $this->candidatureProcessor = new CandidatureProcessor($this->em, $this->validator, $this->tokenStorage, $this->userProvider);
    }

    public function testCandidatureProcessorSuccess(): void
    {
        $operation = new Post();
        $data = [];
        $context = [];

        $user = new User();
        $token = new UsernamePasswordToken($user, 'jwt');
        $this->tokenStorage->setToken($token);
        $this->userProvider->expects($this->once())
            ->method('refreshUser')
            ->with($user)
            ->willReturn($user);

        $dataRequest = [
            'id_offre' => 1,
            'lettre' => 1
        ];
        $dataFile = [
            'file' => $this->createMock(UploadedFile::class)
        ];

        $context['request'] = new Request([], $dataRequest, [], [], $dataFile);

        $lettre = $context['request']->request->get('id_offre');
        $id_offreEmploi = $context['request']->request->get('id_offre');
        $file = $context['request']->files->get('file');

        $offreEmploi = new OffreEmploi();
        $offreEmploiRepository = $this->createMock(OffreEmploiRepository::class);
        $this->em
            ->method('getRepository')
            ->with(OffreEmploi::class)
            ->willReturn($offreEmploiRepository);
        $offreEmploiRepository
            ->method('find')
            ->with($id_offreEmploi)
            ->willReturn($offreEmploi);

        $candidature = $this->candidatureProcessor->setCandidature($user, $id_offreEmploi, $lettre, $file);

        $constraintViolationList = $this->createMock(ConstraintViolationListInterface::class);
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($candidature)
            ->willReturn($constraintViolationList);

        $this->em->expects($this->once())
            ->method('persist')
            ->with($candidature);

        $this->em->expects($this->once())
            ->method('flush');

        $res = $this->candidatureProcessor->process($data, $operation, [], $context);

        $this->assertInstanceOf(Candidature::class, $res);
        $this->assertEquals($offreEmploi, $res->getOffreEmploi());
        $this->assertEquals($lettre, $res->getPieceJointe()->getLettreMotivation());
        $this->assertEquals($file, $res->getPieceJointe()->getCv()->file);
        $this->assertEquals($user, $res->getCandidat());
        $this->assertEquals($user, $res->getPieceJointe()->getOwner());
    }

    public function testCandidatureProcessorThrowException(): void
    {
        $operation = new Post();
        $data = [];
        $context = [];

        $user = new User();
        $token = new UsernamePasswordToken($user, 'jwt');
        $this->tokenStorage->setToken($token);
        $this->userProvider->expects($this->once())
            ->method('refreshUser')
            ->with($user)
            ->willReturn($user);

        $dataRequest = [
            'id_offre' => 1,
            'lettre' => 1
        ];
        $dataFile = [
            'file' => $this->createMock(UploadedFile::class)
        ];

        $context['request'] = new Request([], $dataRequest, [], [], $dataFile);

        $lettre = $context['request']->request->get('id_offre');
        $id_offreEmploi = $context['request']->request->get('id_offre');
        $file = $context['request']->files->get('file');

        $offreEmploi = new OffreEmploi();
        $offreEmploiRepository = $this->createMock(OffreEmploiRepository::class);
        $this->em
            ->method('getRepository')
            ->with(OffreEmploi::class)
            ->willReturn($offreEmploiRepository);
        $offreEmploiRepository
            ->method('find')
            ->with($id_offreEmploi)
            ->willReturn($offreEmploi);

        $candidature = $this->candidatureProcessor->setCandidature($user, $id_offreEmploi, $lettre, $file);

        $constraintViolationList = $this->createMock(ConstraintViolationListInterface::class);
        $constraintViolationList->method('count')->willReturn(1);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($candidature, null, ['groups' => 'post:validator'])
            ->willReturn($constraintViolationList);
        $this->expectException(ValidatorException::class);
        $this->candidatureProcessor->process($data, $operation, [], $context);
    }


    public function testGetAuthenticatedUserSuccess(): void
    {
        $user = (new User());
        $token = new UsernamePasswordToken($user, 'jwt');
        $this->tokenStorage->setToken($token);
        $this->userProvider->expects($this->once())
            ->method('refreshUser')
            ->with($user)
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
        $violations = new ConstraintViolationList();

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn($violations);

        $res = $this->candidatureProcessor->validate($candidature);

        $this->assertTrue($res);
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
        $violation = $this->createMock(ConstraintViolation::class);
        $violations = new ConstraintViolationList([$violation]);

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn($violations);

        $this->expectException(ValidatorException::class);

        $this->candidatureProcessor->validate($candidature);
    }

    public function testSetCandidature(): void
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
    }
}
