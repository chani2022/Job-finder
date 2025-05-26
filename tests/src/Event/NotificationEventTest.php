<?php

namespace App\Tests\src\Event;

use App\Event\NotificationEvent;
use App\Traits\FixturesTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\OffreEmploi;
use App\Repository\AbonnementRepository;

class NotificationEventTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;

    private ?NotificationEvent $notificationEvent;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();

        $this->loadFixturesTrait();

        /** @var OffreEmploi */
        $offreEmploi = $this->all_fixtures['offre_emploi'];
        $abonnementRepository = $this->getContainer()->get(AbonnementRepository::class);

        $this->notificationEvent = new NotificationEvent($offreEmploi, $abonnementRepository);
    }

    public function testGetOffreEmploiEvent(): void
    {
        $res = $this->notificationEvent->getOffreEmploiEvent();

        $this->assertInstanceOf(OffreEmploi::class, $res);
    }

    public function testGetListAbonnement(): void
    {
        $res = $this->notificationEvent->getListAbonnement();

        $this->assertIsArray($res);
        $this->assertCount(11, $res);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
