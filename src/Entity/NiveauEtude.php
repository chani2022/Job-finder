<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\NiveauEtudeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
        Groups([
            'read:get:niveau_etude',
            'read:collection:niveau_etude',
            'read:get:offre',
            'read:collection:offre'
        ])
    ]
    private ?int $id = null;

    #[
        ORM\Column(length: 255, unique: true),
        Groups([
            'read:get:niveau_etude',
            'read:collection:niveau_etude',
            'post:create:niveau_etude',
            'read:get:offre',
            'read:collection:offre'
        ]),
        NotBlank(['groups' => ['post:create:validator']])
    ]
    private ?string $niveau_etude = null;

    /**
     * @var Collection<int, OffreEmploi>
     */
    #[ORM\OneToMany(targetEntity: OffreEmploi::class, mappedBy: 'niveauEtude')]
    private Collection $offreEmplois;

    public function __construct()
    {
        $this->offreEmplois = new ArrayCollection();
    }

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
            $offreEmploi->setNiveauEtude($this);
        }

        return $this;
    }

    public function removeOffreEmploi(OffreEmploi $offreEmploi): static
    {
        if ($this->offreEmplois->removeElement($offreEmploi)) {
            // set the owning side to null (unless already changed)
            if ($offreEmploi->getNiveauEtude() === $this) {
                $offreEmploi->setNiveauEtude(null);
            }
        }

        return $this;
    }
}
