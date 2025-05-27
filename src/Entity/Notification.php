<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => 'read:get:notification', 'read:collection:notification'],
    operations: [
        new Get(
            security: 'object.user == user'
        )
    ]
)]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    #[
        ORM\JoinColumn(nullable: false),
        Groups([
            'read:collection:notification',
            'read:get:notification'
        ])
    ]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    #[
        ORM\JoinColumn(nullable: false),
        Groups([
            'read:collection:notification',
            'read:get:notification'
        ])
    ]
    private ?OffreEmploi $offreEmploi = null;

    #[ORM\Column]
    private ?bool $is_read = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getOffreEmploi(): ?OffreEmploi
    {
        return $this->offreEmploi;
    }

    public function setOffreEmploi(?OffreEmploi $offreEmploi): static
    {
        $this->offreEmploi = $offreEmploi;

        return $this;
    }

    public function isRead(): ?bool
    {
        return $this->is_read;
    }

    public function setIsRead(bool $is_read): static
    {
        $this->is_read = $is_read;

        return $this;
    }
}
