<?php

namespace App\Tests\Src\State\Processor;

use ApiPlatform\Metadata\Post;
use App\Entity\OffreEmploi;
use App\State\Processor\OffreEmploiProcessor;
use App\Traits\FixturesTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class OffreEmploiProcessorTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
        $this->loadFixturesTrait();
    }

    /**
     * @dataProvider getUserAuthorized
     */
    public function testProcessOffreEmploi(?string $roles, bool $access): void
    {
        $data = (new OffreEmploi())
            ->setTitre("mon titre")
            ->setDescription("mon description");
        $operation = new Post();
        $uriVariables = [];
        $context = [];

        $em = $this->getContainer()->get(EntityManagerInterface::class);

        /** @var User|null */
        $user = $this->getUser($roles);
        $tokenStorage = new TokenStorage();
        if ($user) {
            $token = new UsernamePasswordToken($user, 'api', $user->getRoles());
            $tokenStorage->setToken($token);
            if (!$access) {
                $this->expectException(UnauthorizedHttpException::class);
            }
        } else {
            $this->expectException(UnauthorizedHttpException::class);
        }

        $offreEmploiProcessor = new OffreEmploiProcessor($tokenStorage, $em);
        $res = $offreEmploiProcessor->process($data, $operation, $uriVariables, $context);
        if ($access) {
            $this->assertEquals($data->getId(), $res->getId());
            $this->assertEquals($data->getUser(), $res->getUser());
            $this->assertEquals($data->getDateCreatedAt(), $res->getDateCreatedAt());
        }
    }

    public static function getUserAuthorized(): array
    {
        return [
            'super' => ['roles' => 'super', 'access' => true],
            'admin' => ['roles' => 'admin', 'access' => true],
            'user' => ['roles' => 'user', 'access' => false],
            'anonymous' => ['roles' => null, 'access' => false]
        ];
    }

    private function getUser(?string $roles): ?User
    {
        /** @var User */
        $user = match ($roles) {
            'super' => $this->all_fixtures['super'],
            'admin' => $this->all_fixtures['admin_adm_society'],
            'user' => $this->all_fixtures['user_1'],
            default => null
        };

        return $user;
    }
}
