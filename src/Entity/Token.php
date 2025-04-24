<?php

namespace App\Entity;

use App\Repository\TokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Token as ModelToken;

#[ORM\Entity(repositoryClass: TokenRepository::class)]
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
