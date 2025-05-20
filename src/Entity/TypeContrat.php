<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\TypeContratRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: TypeContratRepository::class)]
#[ApiResource(
    denormalizationContext: ['groups' => 'write:create:typeContrat'],
    normalizationContext: ['groups' => ['read:get:typeContrat', 'read:collection:typeContrat']],
    operations: [
        new GetCollection(
            security: 'is_granted("ROLE_SUPER_ADMIN")'
        ),
        new Post(
            security: 'is_granted("ROLE_SUPER_ADMIN")',
            validationContext: ['groups' => ['post:create:validator']],
            denormalizationContext: ['groups' => ['post:create:typeContrat']]
        )
    ]
)]
#[UniqueEntity(fields: ["type_contrat"], groups: ["post:create:validator"])]
class TypeContrat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[
        ORM\Column,
        Groups(['read:get:typeContrat', 'read:collection:typeContrat']),
    ]
    private ?int $id = null;

    #[
        ORM\Column(length: 255),
        NotBlank(['groups' => ['post:create:validator']]),
        Groups(['read:get:typeContrat', 'read:collection:typeContrat', 'post:create:typeContrat']),
    ]
    private ?string $type_contrat = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeContrat(): ?string
    {
        return $this->type_contrat;
    }

    public function setTypeContrat(string $type_contrat): static
    {
        $this->type_contrat = strtoupper($type_contrat);

        return $this;
    }
}
