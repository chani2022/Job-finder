<?php

namespace App\Tests\Src\Controller\User;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\User;
use ApiPlatform\Symfony\Bundle\Test\Response;
use App\Traits\FixturesTrait;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProfilUserControllerTest extends ApiTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;

    protected ?User $user = null;
    private ?Client $client = null;
    private ?JWTTokenManagerInterface $jWTTokenManager;
    private ?EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = new User();
        $this->client = static::createClient();
        $this->jWTTokenManager = static::getContainer()->get(JWTTokenManagerInterface::class);
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->loadFixturesTrait();
    }
    /**
     * @dataProvider providePropsErrors
     */
    public function testProfilDataNotValid($data, $excepted): void
    {
        $user_1 = $this->all_fixtures['user_1'];
        $this->client->loginUser($user_1);

        /** @var Response $response */
        $response = $this->client->request("POST", "/api/profil", [
            'headers' => [
                "content-type" => "multipart/form-data"
            ],
            'extra' => [
                'parameters' => $data, // Vos données JSON (nom, prenom, email, username)
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertCount($excepted, $response->getBrowserKitResponse()->toArray()['violations']);
    }

    public function testProfilNeedRoleUser(): void
    {
        $this->client->request("POST", "/api/profil", [
            'headers' => [
                "content-type" => "multipart/form-data"
            ],
            'extra' => [
                'parameters' => [
                    "username" => "username",
                    "email" => "email@email.com",
                    "nom" => "nom",
                    "prenom" => "prenom"
                ]
            ]
        ]);

        $this->assertResponseStatusCodeSame(401);
    }
    /**
     * @dataProvider provideProfilOk
     */
    public function testProfilOk($data, $file): void
    {
        $user_1 = $this->all_fixtures['user_1'];
        $this->client->loginUser($user_1);

        $extra = [
            'parameters' => $data
        ];
        if ($file['file']) {

            $path_source_file = static::getContainer()->getParameter('path_source_image_test') . 'test.png';
            $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_upload_file.png';
            /**
             * pour ne pas effacer le fichier dans le dossier fixtures
             * lors de l'instance de uploadedfile
             */
            copy($path_source_file, $tmp);
            $file_to_upload = new UploadedFile($tmp, 'test.png');
            $extra['files']['file'] = $file_to_upload;
        }

        /** @var Response $response */
        $response = $this->client->request("POST", "/api/profil", [
            'headers' => [
                "content-type" => "multipart/form-data"
            ],
            "extra" => $extra
        ]);

        $user = $this->em->getRepository(User::class)->find($user_1->getId());

        $this->assertJsonContains(["token" => $response->toArray()['token']]);

        foreach ($data as $attr => $value) {
            $method = 'get' . ucfirst($attr);
            if ($attr == "nom") {
                $value = strtoupper($value);
            } else if ($attr == "prenom") {
                $value = ucwords($value);
            }
            $this->assertEquals($value, call_user_func([$user, $method]));
        }

        if ($file['file']) {

            $path_dest_file = static::getContainer()->getParameter('path_dest_images_test');
            $paths = scandir($path_dest_file);
            $file_name = null;
            foreach ($paths as $r) {
                if ($r != ".." and $r != ".") {
                    if (str_starts_with($r, "test") and str_ends_with($r, ".png")) {
                        $file_name = $path_dest_file . '' . $r;
                    }
                }
            }
            $this->assertFileExists($file_name);
            unlink($file_name);
            $this->assertFileDoesNotExist($file_name);
        }
    }

    public static function provideProfilOk(): array
    {
        return [
            "profil without file" => [
                [
                    "username" => "username",
                    "email" => "email@email.com",
                    "nom" => "nom",
                    "prenom" => "prenom"
                ],
                [
                    "file" => false,
                ]
            ],
            "profil with file" => [
                [
                    "username" => "username",
                    "email" => "email@email.com",
                    "nom" => "nom",
                    "prenom" => "prenom"
                ],
                [
                    "file" => true
                ]
            ]
        ];
    }
    public static function providePropsErrors(): array
    {
        return [
            "nom, prenom, email, username vide" => [
                ["nom" => "", "prenom" => "", "email" => "", "username" => ""],
                4
            ],
            "nom, prenom, email vide" => [
                ["nom" => "", "prenom" => "", "email" => "", "username" => "username"],
                3
            ],
            "nom, prenom vide" => [
                ["nom" => "", "prenom" => "", "email" => "email@email.com", "username" => "username"],
                2
            ],
            "nom vide" => [
                ["nom" => "", "prenom" => "prenom", "email" => "email@email.com", "username" => "username"],
                1
            ],
            "nom et prenom renseigner" => [
                ["nom" => "nom", "prenom" => "prenom", "email" => "", "username" => ""],
                2
            ],
            "nom et email rensegner" => [
                ["nom" => "nom", "prenom" => "", "email" => "email@email.com", "username" => ""],
                2
            ],
            "nom et username rensegner" => [
                ["nom" => "nom", "prenom" => "", "email" => "", "username" => "username"],
                2
            ],
            "prenom et email rensegner" => [
                ["nom" => "", "prenom" => "prenom", "email" => "email@email.com", "username" => ""],
                2
            ],
            "prenom et username rensegner" => [
                ["nom" => "", "prenom" => "prenom", "email" => "", "username" => "username"],
                2
            ],
            "email et username rensegner" => [
                ["nom" => "", "prenom" => "", "email" => "email@email.com", "username" => "username"],
                2
            ],
            "nom renseigner" => [
                ["nom" => "nom", "prenom" => "", "email" => "", "username" => ""],
                3
            ],
            "prenom rensegner" => [
                ["nom" => "", "prenom" => "prenom", "email" => "", "username" => ""],
                3
            ],
            "email rensegner" => [
                ["nom" => "", "prenom" => "", "email" => "email@email.com", "username" => ""],
                3
            ],
            "username rensegner" => [
                ["nom" => "", "prenom" => "", "email" => "", "username" => "username"],
                3
            ],
            "unique email et username" => [
                ["nom" => "nom", "prenom" => "prenom", "email" => "test@test.com", "username" => "test"],
                2
            ],
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->user = null;
        $this->client = null;
        $this->em = null;
        $this->jWTTokenManager = null;
    }
}
