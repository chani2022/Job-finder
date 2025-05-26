<?php

namespace App\Event;

use App\Entity\OffreEmploi;
use App\Repository\AbonnementRepository;
use Symfony\Contracts\EventDispatcher\Event;

class NotificationEvent extends Event
{
    const POST_NOTIFICATION = "post_notification";

    public function __construct(private OffreEmploi $offreEmploi, private AbonnementRepository $abonnementRepository) {}

    public function getOffreEmploiEvent(): OffreEmploi
    {
        return $this->offreEmploi;
    }

    public function getListAbonnement(): array
    {
        return $this->abonnementRepository->findAll();
    }
}
