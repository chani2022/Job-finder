<?php

namespace App\Tests\src\EventSubscriber;

use App\Event\NotificationEvent;
use App\EventSubscriber\NotificationSubscriber;
use App\Repository\AbonnementRepository;
use App\Repository\NotificationRepository;
use App\Traits\FixturesTrait;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Entity\OffreEmploi;
use App\Entity\Notification;
use App\Repository\OffreEmploiRepository;
use App\Entity\Abonnement;
use App\Entity\User;

class NotificationSubscriberTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->loadFixturesTrait();
    }

    public function testOnPostNotification(): void
    {
        $offre_emploi = $this->all_fixtures['offre_emploi'];
        /**
         * pour que doctrine suit l'entity,
         * cela sert à eviter de nouvelle donnée dans la table offreEmploi
         * --------------------
         */
        /** @var OffreEmploiRepository */
        $offre_rep = $this->getContainer()->get(OffreEmploiRepository::class);
        /** @var OffreEmploi */
        $offre_emploi = $offre_rep->find($offre_emploi->getId());
        //---------------------
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        $abonnementRepository = $this->getContainer()->get(AbonnementRepository::class);
        $event = new NotificationEvent($offre_emploi, $abonnementRepository);

        //dispatche l'évènement
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new NotificationSubscriber($em));
        $dispatcher->dispatch($event, NotificationEvent::POST_NOTIFICATION);

        //verifie les assert
        /** @var NotificationRepository */
        $notificationRepository = $this->getContainer()->get(NotificationRepository::class);
        /** @var User */
        $user_abonne = $this->all_fixtures['abonnement_user_adm_society_category_unique']->getUser();
        /** @var Notification */
        $notification = $notificationRepository->findOneBy([
            'user' => $user_abonne,
            'offreEmploi' => $offre_emploi
        ]);
        $this->assertNotNull($notification);
        $this->assertTrue($notification->isRead());
        $this->assertEquals($offre_emploi->getId(), $notification->getOffreEmploi()->getId());
        $this->assertEquals($user_abonne->getId(), $notification->getUser()->getId());
    }

    public function testGetSubscribedEventsNotification(): void
    {
        $this->assertArrayHasKey(NotificationEvent::POST_NOTIFICATION, NotificationSubscriber::getSubscribedEvents());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
