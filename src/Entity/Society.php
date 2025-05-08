<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use App\Repository\SocietyRepository;
use App\State\Society\SocietyPostProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SocietyRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ["read:society:get", "read:society:collection"]],
    denormalizationContext: ['groups' => ['write:society']],
    operations: [
        new Post(
            security: 'is_granted("ROLE_USER")',
            denormalizationContext: ['groups' => ['write:society:post']],
            validationContext: ['groups' => ['post:society:validator']],
            processor: SocietyPostProcessor::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_SUPER_ADMIN")'
        ),
        new Get(
            security: 'is_granted("SOCIETY_VIEW", object)'
        )
    ]
)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['nom_society'])]
#[UniqueEntity(fields: ["nom_society"], groups: ["post:society:validator"])]
class Society
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[
        ORM\Column,
        Groups(["read:society:get", "read:society:collection"])
    ]
    private ?int $id = null;

    #[
        ORM\Column(length: 255),
        Groups(["read:society:get", "read:society:collection", 'write:society:post', 'write:society']),
        Assert\NotBlank(['groups' => ['post:society:validator']])
    ]
    private ?string $nom_society = null;

    /**
     * @var Collection<int, User>
     */
    #[
        ORM\OneToMany(targetEntity: User::class, mappedBy: 'society', cascade: ['persist', 'remove']),
        Groups(["read:society:get", "read:society:collection"])
    ]
    private Collection $users;

    #[ORM\Column(options: ['defaults' => false])]
    private ?bool $status = null;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->status = false;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomSociety(): ?string
    {
        return $this->nom_society;
    }

    public function setNomSociety(string $nom_society): static
    {
        $this->nom_society = strtoupper($nom_society);

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setSociety($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getSociety() === $this) {
                $user->setSociety(null);
            }
        }

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;

        return $this;
    }
}
