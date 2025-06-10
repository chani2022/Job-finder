<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Repository\OffreEmploiRepository;
use App\State\Processor\OffreEmploiProcessor;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: OffreEmploiRepository::class)]
#[ApiResource(
    denormalizationContext: ['groups' => 'write:offre'],
    normalizationContext: ['groups' => 'read:get:offre', 'read:collection:offre'],
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            denormalizationContext: ['groups' => 'post:create:offre'],
            validationContext: ['groups' => ['post:create:validator']],
            processor: OffreEmploiProcessor::class
        )
    ]
)]
class OffreEmploi
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[
        ORM\Column,
        Groups([
            'read:get:offre',
            'read:collection:offre',
            'read:collection:notification',
            'read:get:notification'
        ])
    ]
    private ?int $id = null;

    #[
        ORM\Column(length: 255),
        Groups([
            'post:create:offre',
            'read:get:offre',
            'read:collection:offre',
            'read:collection:notification',
            'read:get:notification'
        ]),
        NotBlank(groups: ['post:create:validator'])
    ]
    private ?string $titre = null;

    #[
        ORM\Column(type: Types::TEXT),
        Groups([
            'post:create:offre',
            'read:get:offre',
            'read:collection:offre',
            'read:collection:notification',
            'read:get:notification'
        ]),
        NotBlank(groups: ['post:create:validator'])
    ]
    private ?string $description = null;

    #[
        ORM\Column,
        Groups([
            'read:get:notification',
            'read:get:offre',
            'read:collection:offre',
        ])
    ]
    private ?\DateTimeImmutable $date_created_at = null;

    #[
        ORM\Column(nullable: true),
        Groups([
            'post:create:offre',
            'read:get:offre',
            'read:collection:offre',
            'read:get:notification'
        ])
    ]
    private ?\DateTimeImmutable $date_expired_at = null;

    #[
        ORM\ManyToOne(inversedBy: 'offreEmplois'),
        Groups([
            'post:create:offre',
            'read:get:offre',
            'read:collection:offre',
            'read:get:notification'
        ])
    ]
    private ?TypeContrat $typeContrat = null;

    #[
        ORM\ManyToOne(inversedBy: 'offreEmplois'),
        Groups([
            'post:create:offre',
            'read:get:offre',
            'read:collection:offre',
            'read:get:notification'
        ])
    ]
    private ?SecteurActivite $secteurActivite = null;

    #[
        ORM\ManyToOne(inversedBy: 'offreEmplois'),
        Groups([
            'post:create:offre',
            'read:get:offre',
            'read:collection:offre',
            'read:get:notification'
        ])
    ]
    private ?NiveauEtude $niveauEtude = null;

    #[
        ORM\ManyToOne(inversedBy: 'offreEmplois'),
        Groups([
            'post:create:offre',
            'read:get:offre',
            'read:collection:offre',
            'read:get:notification'
        ])
    ]
    private ?Experience $experience = null;

    #[ORM\ManyToOne(inversedBy: 'offreEmplois')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'offreEmploi')]
    private Collection $notifications;

    public function __construct()
    {
        $this->date_created_at = new DateTimeImmutable();
        $this->notifications = new ArrayCollection();
    }

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
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setOffreEmploi($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getOffreEmploi() === $this) {
                $notification->setOffreEmploi(null);
            }
        }

        return $this;
    }
}
