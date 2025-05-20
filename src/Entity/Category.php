<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ApiResource(
    denormalizationContext: ['groups' => ['write:post']],
    normalizationContext: ['groups' => ['read:get:category', 'read:collection:category']],
    operations: [
        new GetCollection(
            security: 'is_granted("ROLE_SUPER_ADMIN")'
        ),
        new Post(
            security: 'is_granted("ROLE_SUPER_ADMIN")',
            denormalizationContext: ['groups' => 'post:create:category'],
            validationContext: ['groups' => 'post:create:validator']
        )
    ]
)]
#[UniqueEntity(fields: ['nom_category'], groups: ['post:create:validator'])]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[
        ORM\Column,
        Groups(['read:get:category', 'read:collection:category', 'read:get:secteurActivite', 'read:collection:secteurActivite'])
    ]
    private ?int $id = null;

    #[
        ORM\Column(length: 255),
        Groups(['read:get:category', 'read:collection:category', 'post:create:category', 'read:get:secteurActivite', 'read:collection:secteurActivite']),
        NotBlank(groups: ['post:create:validator'])
    ]
    private ?string $nom_category = null;

    /**
     * @var Collection<int, SecteurActivite>
     */
    #[ORM\OneToMany(targetEntity: SecteurActivite::class, mappedBy: 'category')]
    private Collection $secteurActivites;


    public function __construct()
    {
        $this->secteurActivites = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomCategory(): ?string
    {
        return $this->nom_category;
    }

    public function setNomCategory(string $nom_category): static
    {
        $this->nom_category = $nom_category;

        return $this;
    }

    /**
     * @return Collection<int, SecteurActivite>
     */
    public function getSecteurActivites(): Collection
    {
        return $this->secteurActivites;
    }

    public function addSecteurActivite(SecteurActivite $secteurActivite): static
    {
        if (!$this->secteurActivites->contains($secteurActivite)) {
            $this->secteurActivites->add($secteurActivite);
            $secteurActivite->setCategory($this);
        }

        return $this;
    }

    public function removeSecteurActivite(SecteurActivite $secteurActivite): static
    {
        if ($this->secteurActivites->removeElement($secteurActivite)) {
            // set the owning side to null (unless already changed)
            if ($secteurActivite->getCategory() === $this) {
                $secteurActivite->setCategory(null);
            }
        }

        return $this;
    }
}
