<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ExperienceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExperienceRepository::class)]
#[ApiResource()]
class Experience
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
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
