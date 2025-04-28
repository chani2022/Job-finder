<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\TokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Token as ModelToken;

#[ORM\Entity(repositoryClass: TokenRepository::class)]
#[ApiResource()]
class Token extends ModelToken
{
    // #[ORM\Id]
    // #[ORM\GeneratedValue]
    // #[ORM\Column]
    // private ?int $id = null;

    // public function getId(): ?int
    // {
    //     return $this->id;
    // }
}
