<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ChangePasswordProcessor implements ProcessorInterface
{
    private Security $security;
    private UserPasswordHasherInterface $hasher;
    private JWTTokenManagerInterface $jWTToken;
    private EntityManagerInterface $em;

    public function __construct(
        Security $security,
        UserPasswordHasherInterface $hasher,
        JWTTokenManagerInterface $jWTToken,
        EntityManagerInterface $em
    ) {
        $this->security = $security;
        $this->hasher = $hasher;
        $this->jWTToken = $jWTToken;
        $this->em = $em;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var User $auth */
        $auth = $this->security->getUser();

        $auth = $this->em->getRepository(User::class)->find($auth->getId());

        $auth->setPassword(
            $this->hasher->hashPassword($auth, $data->getConfirmationPassword())
        );

        $this->em->flush();

        $auth->eraseCredentials();

        return new JsonResponse(["token", $this->jWTToken->create($auth)]);
    }
}
