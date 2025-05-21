<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Repository\SecteurActiviteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;
use App\Entity\Category;

#[ApiResource(
    normalizationContext: ['groups' => ['read:get:secteurActivite', 'read:collection:secteurActivite']],
    operations: [
        new GetCollection(
            security: 'is_granted("ROLE_SUPER_ADMIN")'
        ),
        new Post(
            security: 'is_granted("ROLE_SUPER_ADMIN")',
            validationContext: ['groups' => 'post:create:validator']
        ),
        new Get(
            security: 'is_granted("ROLE_SUPER_ADMIN")',
        )

    ]
)]
#[ORM\Entity(repositoryClass: SecteurActiviteRepository::class)]
#[UniqueEntity(fields: ['type_secteur'], groups: ['post:create:validator'])]
class SecteurActivite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[
        ORM\Column,
        Groups(groups: ['read:get:secteurActivite', 'read:collection:secteurActivite'])
    ]
    private ?int $id = null;

    #[
        ORM\Column(length: 255),
        NotBlank(groups: ['post:create:validator']),
        Groups(groups: ['read:get:secteurActivite', 'read:collection:secteurActivite'])
    ]
    private ?string $type_secteur = null;

    #[
        ORM\ManyToOne(inversedBy: 'secteurActivites'),
        Groups(groups: ['read:get:secteurActivite', 'read:collection:secteurActivite'])
    ]
    private ?Category $category = null;

    /**
     * @var Collection<int, OffreEmploi>
     */
    #[ORM\OneToMany(targetEntity: OffreEmploi::class, mappedBy: 'secteurActivite')]
    private Collection $offreEmplois;

    public function __construct()
    {
        $this->offreEmplois = new ArrayCollection();
    }


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

    /**
     * @return Collection<int, OffreEmploi>
     */
    public function getOffreEmplois(): Collection
    {
        return $this->offreEmplois;
    }

    public function addOffreEmploi(OffreEmploi $offreEmploi): static
    {
        if (!$this->offreEmplois->contains($offreEmploi)) {
            $this->offreEmplois->add($offreEmploi);
            $offreEmploi->setSecteurActivite($this);
        }

        return $this;
    }

    public function removeOffreEmploi(OffreEmploi $offreEmploi): static
    {
        if ($this->offreEmplois->removeElement($offreEmploi)) {
            // set the owning side to null (unless already changed)
            if ($offreEmploi->getSecteurActivite() === $this) {
                $offreEmploi->setSecteurActivite(null);
            }
        }

        return $this;
    }
}
