<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CandidatureRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use App\State\Processor\Candidature\CandidatureProcessor;
use App\Entity\User;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: CandidatureRepository::class)]
#[ApiResource(
    normalizationContext: [
        'groups' => [
            'read:get:candidature',
            'read:collection:candidature'
        ]
    ],
    operations: [
        new Post(
            inputFormats: ['multipart' => ['multipart/form-data']],
            security: "is_granted('ROLE_USER')",
            uriTemplate: "/postuler/offre",
            processor: CandidatureProcessor::class,
            deserialize: false,
            validationContext: ["groups" => "post:validator"],
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
                                    'offreEmploi' => [
                                        'type' => 'string',
                                        'format' => 'string'
                                    ],
                                    'lettre' => [
                                        'type' => 'string',
                                        'format' => 'string'
                                    ]
                                ]
                            ]
                        ]
                    ])
                )
            )
        ),
    ]
)]
class Candidature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[
        ORM\Column,
        Groups([
            'read:get:candidature',
            'read:collection:candidature'
        ])
    ]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'candidatures')]
    #[
        ORM\JoinColumn(nullable: false),
        Groups([
            'read:get:candidature',
            'read:collection:candidature',
        ]),
        NotBlank(groups: ['post:validator'])
    ]
    private ?User $candidat = null;

    #[ORM\ManyToOne(inversedBy: 'candidatures')]
    #[
        ORM\JoinColumn(nullable: false),
        Groups([
            'read:get:candidature',
            'read:collection:candidature',
            'post:candidature'
        ]),
        NotBlank(groups: ['post:validator'])
    ]
    private ?OffreEmploi $offreEmploi = null;

    #[ORM\ManyToOne(inversedBy: 'candidatures', cascade: ['persist'])]
    #[
        ORM\JoinColumn(nullable: false),
        Groups([
            'read:get:candidature',
            'read:collection:candidature'
        ]),
        NotBlank(groups: ['post:validator'])
    ]
    private ?PieceJointe $pieceJointe = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCandidat(): ?User
    {
        return $this->candidat;
    }

    public function setCandidat(?User $candidat): static
    {
        $this->candidat = $candidat;

        return $this;
    }

    public function getOffreEmploi(): ?OffreEmploi
    {
        return $this->offreEmploi;
    }

    public function setOffreEmploi(?OffreEmploi $offreEmploi): static
    {
        $this->offreEmploi = $offreEmploi;

        return $this;
    }

    public function getPieceJointe(): ?PieceJointe
    {
        return $this->pieceJointe;
    }

    public function setPieceJointe(?PieceJointe $pieceJointe): static
    {
        $this->pieceJointe = $pieceJointe;

        return $this;
    }
}
