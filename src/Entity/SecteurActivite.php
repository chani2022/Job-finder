<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\SecteurActiviteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ApiResource(
    operations: [
        new GetCollection(
            security: 'is_granted("ROLE_SUPER_ADMIN")'
        ),
        new Post(
            security: 'is_granted("ROLE_SUPER_ADMIN")',
            validationContext: ['groups' => 'post:create:validator']
        )

    ]
)]
#[ORM\Entity(repositoryClass: SecteurActiviteRepository::class)]
#[UniqueEntity(fields: ['type_secteur'], groups: ['post:create:validator'])]
class SecteurActivite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[
        ORM\Column(length: 255),
        NotBlank(groups: ['post:create:validator'])
    ]
    private ?string $type_secteur = null;

    #[ORM\ManyToOne(inversedBy: 'secteurActivites')]
    private ?Category $category = null;

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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }
}
