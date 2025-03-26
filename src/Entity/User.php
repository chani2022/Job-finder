<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\UserRepository;
use App\State\PostUserProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ApiResource(
    inputFormats: [
        "json" => ["application/json"]
    ],
    outputFormats: [
        'jsonld' => ['application/ld+json'],
    ],
    normalizationContext: ["groups" => ["read:user:get", "read:user:collection"], 'skip_null_values' => false],
    denormalizationContext: ["groups" => ["write:user"]],
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            denormalizationContext: ["groups" => ["post:create:user"]],
            validationContext: ["groups" => ["post:create:validator"]],
            processor: PostUserProcessor::class
        ),
        new Put(),
    ]
)]

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email', "username"])]
#[UniqueEntity(fields: ["email"], groups: ["post:create:validator"])]
#[UniqueEntity(fields: ["username"], groups: ["post:create:validator"])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[
        ORM\Column,
        Groups(["read:user:get", "read:user:collection"])
    ]
    private ?int $id = null;

    #[
        ORM\Column(length: 180),
        Groups(["read:user:get", "read:user:collection", "post:create:user"]),
        Assert\NotBlank(groups: ["post:create:validator"]),
        Assert\Email(groups: ["post:create:validator"])
    ]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[
        ORM\Column,
        Groups(["read:user:get", "read:user:collection"])
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
        Groups(["read:user:get", "read:user:collection"])
    ]
    private ?string $nom = null;

    #[
        ORM\Column(length: 255, nullable: true),
        Groups(["read:user:get", "read:user:collection"])
    ]
    private ?string $prenom = null;

    #[
        Groups(["post:create:user"]),
        SerializedName("Mot de passe"),
        Assert\NotBlank(groups: ["post:create:validator"])
    ]
    public ?string $plainPassword = null;

    #[
        Groups(["post:create:user"]),
        SerializedName("Confirmez votre mot de passe"),
        Assert\NotBlank(groups: ["post:create:validator"]),
        Assert\EqualTo(propertyPath: "plainPassword", groups: ["post:create:validator"])
    ]
    public ?string $confirmationPassword = null;

    #[
        Groups(["read:user:get", "read:user:collection", "post:create:user"]),
        ORM\Column(length: 255),
        Assert\NotBlank(groups: ["post:create:validator"]),
    ]
    private ?string $username = null;

    public function getId(): ?int
    {
        return $this->id;
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
}
