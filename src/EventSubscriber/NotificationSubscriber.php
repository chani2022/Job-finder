<?php

namespace App\EventSubscriber;

use App\Event\NotificationEvent;
use App\Entity\Abonnement;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(private EntityManagerInterface $em) {}
    public function onPostNotification(NotificationEvent $event): void
    {
        $offreEmploi = $event->getOffreEmploiEvent();
        $listAbonnement = $event->getListAbonnement();
        /** @var Abonnement $abonnement */
        foreach ($listAbonnement as $abonnement) {
            foreach ($abonnement->getCategory() as $category) {
                if ($offreEmploi->getSecteurActivite()?->getCategory()->getId() == $category->getId()) {
                    $notification = (new Notification())
                        ->setUser($abonnement->getUser())
                        ->setOffreEmploi($offreEmploi)
                        ->setIsRead(true);
                    $this->em->persist($notification);
                }
            }
        }
        $this->em->flush();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NotificationEvent::POST_NOTIFICATION => 'onPostNotification',
        ];
    }
}
