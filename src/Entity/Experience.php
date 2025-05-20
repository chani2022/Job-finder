<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\ExperienceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: ExperienceRepository::class)]
#[ApiResource(
    denormalizationContext: ['groups' => 'post:create:experience'],
    normalizationContext: ['groups' => ['read:get:experience', 'read:collection:experience']],
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
#[UniqueEntity(fields: ["nombre_experience"], groups: ["post:create:validator"])]
class Experience
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[
        ORM\Column,
        Groups(['read:get:experience', 'read:collection:experience'])
    ]
    private ?int $id = null;

    #[
        ORM\Column(length: 255),
        NotBlank(['groups' => ['post:create:validator']]),
        Groups(['read:get:experience', 'read:collection:experience', 'post:create:experience'])
    ]
    private ?string $nombre_experience = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreExperience(): ?string
    {
        return $this->nombre_experience;
    }

    public function setNombreExperience(string $nombre_experience): static
    {
        $this->nombre_experience = $nombre_experience;

        return $this;
    }
}
