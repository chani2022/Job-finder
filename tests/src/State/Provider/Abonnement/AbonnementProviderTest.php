<?php

namespace App\Tests\src\State\Provider\Abonnement;

use ApiPlatform\Metadata\GetCollection;
use App\State\Provider\Abonnement\AbonnementProvider;
use App\Traits\FixturesTrait;
use App\Traits\TokenStorageTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\User;
use App\Repository\AbonnementRepository;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AbonnementProviderTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;
    use TokenStorageTrait;


    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
        $this->loadFixturesTrait();
    }

    /**
     * @dataProvider getUsers
     */
    public function testProvideAbonnement(string $roles, ?bool $access): void
    {
        $user = $this->getUser($roles);
        $this->logUser($user);

        $abonnementRepository = self::getContainer()->get(AbonnementRepository::class);
        $abonnementProvider = new AbonnementProvider($this->tokenStorage, $abonnementRepository);

        $operation = new GetCollection();

        $res = $abonnementProvider->provide($operation);

        if ($roles == "super") {
            $this->assertCount(11, $res);
        } else if ($roles == "owner") {
            $this->assertCount(1, $res);
            $this->assertCount(2, $res[0]->getCategory());
        } else {
            $this->assertCount(0, $res);
        }
    }

    public function testProviderAbonnementThrowUnauthorized(): void
    {
        $abonnementRepository = self::getContainer()->get(AbonnementRepository::class);
        $tokenStorage = static::getContainer()->get(TokenStorageInterface::class);
        $abonnementProvider = new AbonnementProvider($tokenStorage, $abonnementRepository);

        $this->expectException(UnauthorizedHttpException::class);
        $operation = new GetCollection();
        $abonnementProvider->provide($operation);
    }

    private function getUser(?string $roles): ?User
    {
        /** @var User */
        $user = match ($roles) {
            'super' => $this->all_fixtures['super'],
            'owner' => $this->all_fixtures['user_adm_society'],
            'user' => $this->all_fixtures['user_1'],
            default => null
        };

        return $user;
    }

    public static function getUsers(): array
    {
        return [
            'super' => ['roles' => 'super', 'access' => true],
            'user_owner' => ['roles' => 'owner', 'access' => true],
            'user_other' => ['roles' => 'user', 'access' => null]
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
