<?php

namespace App\Tests\Src\State\Processor;

use ApiPlatform\Metadata\Post;
use App\Entity\Notification;
use App\Entity\OffreEmploi;
use App\State\Processor\OffreEmploiProcessor;
use App\Traits\FixturesTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use App\Entity\User;
use App\Repository\AbonnementRepository;
use App\Repository\NotificationRepository;
use App\Repository\SecteurActiviteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
    public function testProcessOffreEmploi(?string $roles, bool $access, bool $withSecteurActivite): void
    {
        $data = (new OffreEmploi())
            ->setTitre("mon titre")
            ->setDescription("mon description");
        if ($withSecteurActivite) {
            $secteurActivite = $this->all_fixtures['secteur_unique'];
            //Pour eviter l'insertion de secteurActivite entity
            $secteurActivite = $this->getContainer()->get(SecteurActiviteRepository::class)->find($secteurActivite->getId());
            $data->setSecteurActivite($secteurActivite);
        }
        $operation = new Post();
        $uriVariables = [];
        $context = [];

        //dependance offreEmploi
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        $eventDispatcher = $this->getContainer()->get(EventDispatcherInterface::class);
        $abonnementRepository = $this->getContainer()->get(AbonnementRepository::class);

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

        $offreEmploiProcessor = new OffreEmploiProcessor($tokenStorage, $em, $eventDispatcher, $abonnementRepository);
        $newOffreEmploi = $offreEmploiProcessor->process($data, $operation, $uriVariables, $context);
        if ($access) {
            $this->assertEquals($data->getId(), $newOffreEmploi->getId());
            $this->assertEquals($data->getUser(), $newOffreEmploi->getUser());
            $this->assertEquals($data->getDateCreatedAt(), $newOffreEmploi->getDateCreatedAt());
            /** @var NotificationRepository */
            $notificationRepository = $this->getContainer()->get(NotificationRepository::class);
            if ($withSecteurActivite) {
                $notification = $notificationRepository->findOneBy([
                    'user' => $this->all_fixtures['abonnement_user_adm_society_category_unique']->getUser(),
                    'offreEmploi' => $newOffreEmploi
                ]);

                $this->assertInstanceOf(Notification::class, $notification);
                $this->assertNotNull($notification);
            } else {
                $notification = $notificationRepository->findOneBy([
                    'offreEmploi' => $newOffreEmploi
                ]);
                $this->assertNull($notification);
            }
        }
    }

    public static function getUserAuthorized(): array
    {
        return [
            'super' => ['roles' => 'super', 'access' => true, 'withSecteurActivite' => false],
            'admin' => ['roles' => 'admin', 'access' => true, 'withSecteurActivite' => true],
            'user' => ['roles' => 'user', 'access' => false, 'withSecteurActivite' => false],
            'anonymous' => ['roles' => null, 'access' => false, 'withSecteurActivite' => false]
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
