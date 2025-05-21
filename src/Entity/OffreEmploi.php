<?php

namespace App\Entity;

use App\Repository\OffreEmploiRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OffreEmploiRepository::class)]
class OffreEmploi
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date_created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $date_expired_at = null;

    #[ORM\ManyToOne(inversedBy: 'offreEmplois')]
    private ?TypeContrat $typeContrat = null;

    #[ORM\ManyToOne(inversedBy: 'offreEmplois')]
    private ?SecteurActivite $secteurActivite = null;

    #[ORM\ManyToOne(inversedBy: 'offreEmplois')]
    private ?NiveauEtude $niveauEtude = null;

    #[ORM\ManyToOne(inversedBy: 'offreEmplois')]
    private ?Experience $experience = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDateCreatedAt(): ?\DateTimeImmutable
    {
        return $this->date_created_at;
    }

    public function setDateCreatedAt(\DateTimeImmutable $date_created_at): static
    {
        $this->date_created_at = $date_created_at;

        return $this;
    }

    public function getDateExpiredAt(): ?\DateTimeImmutable
    {
        return $this->date_expired_at;
    }

    public function setDateExpiredAt(?\DateTimeImmutable $date_expired_at): static
    {
        $this->date_expired_at = $date_expired_at;

        return $this;
    }

    public function getTypeContrat(): ?TypeContrat
    {
        return $this->typeContrat;
    }

    public function setTypeContrat(?TypeContrat $typeContrat): static
    {
        $this->typeContrat = $typeContrat;

        return $this;
    }

    public function getSecteurActivite(): ?SecteurActivite
    {
        return $this->secteurActivite;
    }

    public function setSecteurActivite(?SecteurActivite $secteurActivite): static
    {
        $this->secteurActivite = $secteurActivite;

        return $this;
    }

    public function getNiveauEtude(): ?NiveauEtude
    {
        return $this->niveauEtude;
    }

    public function setNiveauEtude(?NiveauEtude $niveauEtude): static
    {
        $this->niveauEtude = $niveauEtude;

        return $this;
    }

    public function getExperience(): ?Experience
    {
        return $this->experience;
    }

    public function setExperience(?Experience $experience): static
    {
        $this->experience = $experience;

        return $this;
    }
}
