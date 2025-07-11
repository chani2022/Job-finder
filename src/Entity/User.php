<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use App\Repository\UserRepository;
use App\State\ChangePasswordProcessor;
use App\State\DisabledUserProcessor;
use App\State\PostUserProcessor;
use App\State\ProfilUserProcessor;
use App\State\Provider\User\UserProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;


#[ApiResource(
    inputFormats: [
        "json" => ["application/json"]
    ],
    outputFormats: [
        'jsonld' => ['application/ld+json'],
    ],
    normalizationContext: [
        "groups" => [
            "read:user:get",
            "read:user:collection"
        ],
        'skip_null_values' => false
    ],
    denormalizationContext: ["groups" => ["write:user"]],
    operations: [
        new GetCollection(
            provider: UserProvider::class,
            security: "is_granted('ROLE_SUPER_ADMIN')"
        ),
        new Get(
            provider: UserProvider::class,
            security: "is_granted('GET_VIEW', object)"
        ),
        new Post(
            denormalizationContext: ["groups" => ["post:create:user"]],
            validationContext: ["groups" => ["post:create:validator"]],
            processor: PostUserProcessor::class
        ),
        new Put(),
        new Post(
            inputFormats: ['multipart' => ['multipart/form-data']],
            security: "is_granted('ROLE_USER')",
            uriTemplate: "/profil",
            processor: ProfilUserProcessor::class,
            deserialize: false,
            validationContext: ["groups" => "profil:validator"],
            openapi: new Operation(
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'file' => [
                                        'type' => 'string',
                                        'format' => 'binary'
                                    ],
                                    'nom' => [
                                        'type' => 'string',
                                        'format' => 'string'
                                    ],
                                    'prenom' => [
                                        'type' => 'string',
                                        'format' => 'string'
                                    ],
                                    'email' => [
                                        'type' => 'string',
                                        'format' => 'email'
                                    ],
                                    'username' => [
                                        'type' => 'string',
                                        'format' => 'string'
                                    ]
                                ]
                            ]
                        ]
                    ])
                ),
                responses: [
                    200 => [
                        'description' => 'Profil modifiez avec success',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'token' => ['type' => 'string', 'example' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...'],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            )
        ),
        new Put(
            security: "is_granted('ROLE_USER')",
            securityMessage: "unAuthorized",
            uriTemplate: "/change-password",
            name: "api_change_password",
            processor: ChangePasswordProcessor::class,
            denormalizationContext: ["groups" => ["put:changePassword:user"]],
            validationContext: ["groups" => ["put:changePassword:validator"]],
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
            uriTemplate: "/disabled/{id}",
            name: "api_disabled_user",
            processor: DisabledUserProcessor::class,
            openapi: new Operation(
                summary: "Pour desactiver un utilisateur",
            )

        ),
        new Delete()
    ],
    graphQlOperations: [
        new Query(),
        new QueryCollection(),
        new Mutation(name: 'create'),
        new Mutation(name: 'update'),
        new Mutation(name: 'delete')
    ]
)]

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email', "username"])]
#[UniqueEntity(fields: ['email'], groups: ["post:create:validator", "profil:validator"])]
#[UniqueEntity(fields: ["username"], groups: ["post:create:validator", "profil:validator"])]
class User implements UserInterface, PasswordAuthenticatedUserInterface, JWTUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[
        ORM\Column,
        Groups([
            "read:user:get",
            "read:user:collection",
            "read:society:get",
            "read:society:collection",
            "read:society:get",
            "read:society:collection",
            'read:collection:abonnement'
        ])
    ]
    private ?int $id = null;

    #[
        ORM\Column(length: 180),
        Groups([
            "read:user:get",
            "read:user:collection",
            "post:create:user",
            "read:society:get",
            "read:society:collection",
            'read:collection:abonnement'
        ]),
        Assert\NotBlank(groups: ["post:create:validator", "profil:validator"]),
        Assert\Email(groups: ["post:create:validator", "profil:validator"])
    ]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[
        ORM\Column,
        Groups(["read:user:get", "read:user:collection", "read:society:get", "read:society:collection", "read:society:get", "read:society:collection"])
    ]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[
        ORM\Column,
        Groups(["read:user:get", "read:user:collection"]),
    ]
    private ?string $password = null;

    #[
        ORM\Column(length: 255, nullable: true),
        Groups([
            "read:user:get",
            "read:user:collection",
            'read:collection:abonnement'
        ]),
        Assert\NotBlank(groups: ["profil:validator"])
    ]
    private ?string $nom = null;

    #[
        ORM\Column(length: 255, nullable: true),
        Groups([
            "read:user:get",
            "read:user:collection",
            "read:society:get",
            "read:society:collection",
            'read:collection:abonnement'
        ]),
        Assert\NotBlank(groups: ["profil:validator"])
    ]
    private ?string $prenom = null;

    #[
        Groups(["post:create:user", "put:changePassword:user"]),
        SerializedName("password"),
        Assert\NotBlank(groups: ["post:create:validator", "put:changePassword:validator"]),
        SecurityAssert\UserPassword(
            message: 'Wrong value for your current password',
            groups: ['put:changePassword:validator']
        )
    ]
    private ?string $plainPassword = null;

    #[
        Groups(["put:changePassword:user"]),
        Assert\NotBlank(groups: ["put:changePassword:validator"])
    ]
    private ?string $newPassword = null;

    #[
        Groups(["post:create:user", "put:changePassword:user"]),
        Assert\NotBlank(groups: ["post:create:validator", "put:changePassword:validator"]),
        Assert\EqualTo(propertyPath: "plainPassword", groups: ["post:create:validator"]),
        Assert\EqualTo(propertyPath: "newPassword", groups: ["put:changePassword:validator"])
    ]
    public ?string $confirmationPassword = null;

    #[
        Groups([
            "read:user:get",
            "read:user:collection",
            "post:create:user",
            "read:society:get",
            "read:society:collection",
            'read:collection:abonnement'
        ]),
        ORM\Column(length: 255),
        Assert\NotBlank(groups: ["post:create:validator", "profil:validator"]),
    ]
    private ?string $username = null;

    #[ORM\OneToOne(targetEntity: MediaObject::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    #[ApiProperty(types: ['https://schema.org/image'])]
    #[
        Groups([
            "read:user:get",
            "read:user:collection",
            'read:collection:abonnement'
        ])
    ]
    public ?MediaObject $image = null;

    #[
        ORM\Column(type: 'boolean', options: ['default' => false]),
        Groups(["read:user:get", "read:user:collection"])
    ]
    private ?bool $status = null;

    #[
        ORM\ManyToOne(inversedBy: 'users'),
        Groups(["read:user:get", "read:user:collection"])
    ]
    private ?Society $society = null;

    /**
     * @var Collection<int, Abonnement>
     */
    #[ORM\OneToMany(targetEntity: Abonnement::class, mappedBy: 'user')]
    private Collection $abonnements;

    /**
     * @var Collection<int, OffreEmploi>
     */
    #[ORM\OneToMany(targetEntity: OffreEmploi::class, mappedBy: 'user')]
    private Collection $offreEmplois;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'user')]
    private Collection $notifications;

    /**
     * @var Collection<int, Candidature>
     */
    #[ORM\OneToMany(targetEntity: Candidature::class, mappedBy: 'candidat')]
    private Collection $candidatures;

    /**
     * @var Collection<int, PieceJointe>
     */
    #[ORM\OneToMany(targetEntity: PieceJointe::class, mappedBy: 'owner')]
    private Collection $pieceJointes;

    public function __construct()
    {
        $this->status = false;
        $this->abonnements = new ArrayCollection();
        $this->offreEmplois = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->candidatures = new ArrayCollection();
        $this->pieceJointes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
        $this->confirmationPassword = null;
        $this->newPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function setNewPassword(?string $newPassword): static
    {
        $this->newPassword = $newPassword;

        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setConfirmationPassword(?string $confirmPassword): static
    {
        $this->confirmationPassword = $confirmPassword;

        return $this;
    }

    public function getConfirmationPassword(): ?string
    {
        return $this->confirmationPassword;
    }

    public static function createFromPayload($id, array $payload)
    {
        $user =  new User();
        $user->setId($id);
        foreach ($payload as $prop => $value) {
            $method = 'set' . ucfirst($prop);
            if (method_exists($user, $method)) {
                call_user_func([$user, $method], $value);
            }
        }


        return $user;
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

    public function getSociety(): ?Society
    {
        return $this->society;
    }

    public function setSociety(?Society $society): static
    {
        $this->society = $society;

        return $this;
    }

    /**
     * @return Collection<int, Abonnement>
     */
    public function getAbonnements(): Collection
    {
        return $this->abonnements;
    }

    public function addAbonnement(Abonnement $abonnement): static
    {
        if (!$this->abonnements->contains($abonnement)) {
            $this->abonnements->add($abonnement);
            $abonnement->setUser($this);
        }

        return $this;
    }

    public function removeAbonnement(Abonnement $abonnement): static
    {
        if ($this->abonnements->removeElement($abonnement)) {
            // set the owning side to null (unless already changed)
            if ($abonnement->getUser() === $this) {
                $abonnement->setUser(null);
            }
        }

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
            $offreEmploi->setUser($this);
        }

        return $this;
    }

    public function removeOffreEmploi(OffreEmploi $offreEmploi): static
    {
        if ($this->offreEmplois->removeElement($offreEmploi)) {
            // set the owning side to null (unless already changed)
            if ($offreEmploi->getUser() === $this) {
                $offreEmploi->setUser(null);
            }
        }

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
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

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
            $candidature->setCandidat($this);
        }

        return $this;
    }

    public function removeCandidature(Candidature $candidature): static
    {
        if ($this->candidatures->removeElement($candidature)) {
            // set the owning side to null (unless already changed)
            if ($candidature->getCandidat() === $this) {
                $candidature->setCandidat(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PieceJointe>
     */
    public function getPieceJointes(): Collection
    {
        return $this->pieceJointes;
    }

    public function addPieceJointe(PieceJointe $pieceJointe): static
    {
        if (!$this->pieceJointes->contains($pieceJointe)) {
            $this->pieceJointes->add($pieceJointe);
            $pieceJointe->setOwner($this);
        }

        return $this;
    }

    public function removePieceJointe(PieceJointe $pieceJointe): static
    {
        if ($this->pieceJointes->removeElement($pieceJointe)) {
            // set the owning side to null (unless already changed)
            if ($pieceJointe->getOwner() === $this) {
                $pieceJointe->setOwner(null);
            }
        }

        return $this;
    }
}
