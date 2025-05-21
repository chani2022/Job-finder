<?php

namespace App\Tests\src\Repository;

use App\Repository\AbonnementRepository;
use App\Traits\FixturesTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AbonnementRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;

    private ?AbonnementRepository $abonnementRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $container = static::getContainer();
        $this->loadFixturesTrait();

        $this->abonnementRepository = $container->get(AbonnementRepository::class);
    }

    public function testFindAbonnementsOwner(): void
    {
        $user = $this->all_fixtures['user_adm_society'];
        $res = $this->abonnementRepository->findAbonnementsOwner($user);

        $this->assertCount(1, $res);
        $this->assertCount(2, $res[0]->getCategory());
    }

    public function testFindAllAbonnements(): void
    {
        $res = $this->abonnementRepository->findAllAbonnements();

        $this->assertCount(11, $res);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->abonnementRepository = null;
    }
}
