<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\AbonnementRepository;
use App\State\Provider\Abonnement\AbonnementProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\Collection as ConstraintsCollection;
use Symfony\Component\Validator\Constraints\Unique;

#[ORM\Entity(repositoryClass: AbonnementRepository::class)]
#[ApiResource(
    normalizationContext: [
        'groups' => [
            'read:get:abonnement',
            'read:collection:abonnement'
        ],
        'skip_null_values' => false
    ],
    operations: [
        new GetCollection(
            provider: AbonnementProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_USER")'
        ),
        new Delete(
            security: 'is_granted("ROLE_USER")'
        )
    ]
)]
class Abonnement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[
        ORM\Column,
        Groups([
            'read:get:abonnement',
            'read:collection:abonnement'
        ])
    ]
    private ?int $id = null;

    #[
        ORM\ManyToOne(inversedBy: 'abonnements'),
        Groups([
            'read:get:abonnement',
            'read:collection:abonnement'
        ])
    ]
    private ?User $user = null;

    /**
     * @var Collection<int, Category>
     */
    #[
        ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'abonnements'),
        Groups([
            'read:get:abonnement',
            'read:collection:abonnement'
        ]),
    ]
    private Collection $category;

    public function __construct()
    {
        $this->category = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategory(): Collection
    {
        return $this->category;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->category->contains($category)) {
            $this->category->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->category->removeElement($category);

        return $this;
    }
}
