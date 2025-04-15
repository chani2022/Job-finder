<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Mailer\ServiceMailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PostUserProcessor implements ProcessorInterface
{
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $hasher;
    private ServiceMailer $serviceMailer;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $hasher, ServiceMailer $serviceMailer)
    {
        $this->em = $em;
        $this->hasher = $hasher;
        $this->serviceMailer = $serviceMailer;
    }
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if (!$data instanceof User) return;
        // Handle the state
        $data->setPassword(
            $this->hasher->hashPassword($data, $data->getConfirmationPassword())
        );

        $data->eraseCredentials();

        $this->em->persist($data);
        $this->em->flush();
        //envoye d'email
        $this->serviceMailer->send($data, "Confirmation");

        return $data;
    }
}
