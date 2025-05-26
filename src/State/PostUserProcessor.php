<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Entity\User;
use App\Mailer\ServiceMailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    /**
     * @param User $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if (!$data instanceof User) return;

        // $errors = $this->validator->validate($data, null, ['post:create:validator']);

        // if (count($errors) > 0) {
        //     throw new ValidationException($errors);
        // }
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
