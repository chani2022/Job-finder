<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\SecteurActiviteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    operations: [
        new GetCollection(
            security: 'is_granted("ROLE_SUPER_ADMIN")'
        ),
        new Post(
            security: 'is_granted("ROLE_SUPER_ADMIN")'
        )

    ]
)]
#[ORM\Entity(repositoryClass: SecteurActiviteRepository::class)]
class SecteurActivite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type_secteur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeSecteur(): ?string
    {
        return $this->type_secteur;
    }

    public function setTypeSecteur(string $type_secteur): static
    {
        $this->type_secteur = strtoupper($type_secteur);

        return $this;
    }
}
