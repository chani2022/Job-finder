<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Payment as ModelPayment;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment extends ModelPayment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
