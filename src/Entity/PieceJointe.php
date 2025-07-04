<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PieceJointeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: PieceJointeRepository::class)]
#[ApiResource()]
class PieceJointe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[
        ORM\Column,
        Groups([
            'read:get:candidature',
            'read:collection:candidature',
        ])
    ]
    private ?int $id = null;

    #[
        ORM\Column(type: Types::TEXT),
        Groups([
            'read:get:candidature',
            'read:collection:candidature',
        ]),
        NotBlank(groups: ['post:validator'])
    ]
    private ?string $lettreMotivation = null;

    #[ORM\ManyToOne(inversedBy: 'pieceJointes')]
    #[
        ORM\JoinColumn(nullable: false),
        NotBlank(groups: ['post:validator'])
    ]
    private ?User $owner = null;

    #[ORM\ManyToOne(inversedBy: 'pieceJointes', cascade: ['persist'])]
    #[
        ORM\JoinColumn(nullable: false),
        Groups([
            'read:get:candidature',
            'read:collection:candidature',
        ]),
        NotBlank(groups: [
            'post:validator'
        ])
    ]
    private ?MediaObject $cv = null;

    /**
     * @var Collection<int, Candidature>
     */
    #[ORM\OneToMany(targetEntity: Candidature::class, mappedBy: 'pieceJointe')]
    private Collection $candidatures;

    public function __construct()
    {
        $this->candidatures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLettreMotivation(): ?string
    {
        return $this->lettreMotivation;
    }

    public function setLettreMotivation(string $lettreMotivation): static
    {
        $this->lettreMotivation = $lettreMotivation;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getCv(): ?MediaObject
    {
        return $this->cv;
    }

    public function setCv(?MediaObject $cv): static
    {
        $this->cv = $cv;

        return $this;
    }

    /**
     * @return Collection<int, Candidature>
     */
    public function getCandidatures(): Collection
    {
        return $this->candidatures;
    }

    public function addCandidature(Candidature $candidature): static
    {
        if (!$this->candidatures->contains($candidature)) {
            $this->candidatures->add($candidature);
            $candidature->setPieceJointe($this);
        }

        return $this;
    }

    public function removeCandidature(Candidature $candidature): static
    {
        if ($this->candidatures->removeElement($candidature)) {
            // set the owning side to null (unless already changed)
            if ($candidature->getPieceJointe() === $this) {
                $candidature->setPieceJointe(null);
            }
        }

        return $this;
    }
}
