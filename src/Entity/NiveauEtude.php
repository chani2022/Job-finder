<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\NiveauEtudeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;


#[ApiResource(
    denormalizationContext: ['groups' => 'post:create:niveau_etude'],
    normalizationContext: ['groups' => ['read:get:niveau_etude', 'read:collection:niveau_etude']],
    operations: [
        new GetCollection(
            security: 'is_granted("ROLE_SUPER_ADMIN")'
        ),
        new Post(
            security: 'is_granted("ROLE_SUPER_ADMIN")',
            validationContext: ['groups' => ['post:create:validator']]
        )
    ]
)]
#[ORM\Entity(repositoryClass: NiveauEtudeRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_NIVEAU_ETUDE', fields: ['niveau_etude'])]
#[UniqueEntity(fields: ["niveau_etude"], groups: ["post:create:validator"])]
class NiveauEtude
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[
        ORM\Column,
        Groups(['read:get:niveau_etude', 'read:collection:niveau_etude'])
    ]
    private ?int $id = null;

    #[
        ORM\Column(length: 255, unique: true),
        Groups(['read:get:niveau_etude', 'read:collection:niveau_etude', 'post:create:niveau_etude']),
        NotBlank(['groups' => ['post:create:validator']])
    ]
    private ?string $niveau_etude = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNiveauEtude(): ?string
    {
        return $this->niveau_etude;
    }

    public function setNiveauEtude(string $niveau_etude): static
    {
        $this->niveau_etude = strtoupper($niveau_etude);

        return $this;
    }
}
