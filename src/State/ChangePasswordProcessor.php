<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ChangePasswordProcessor implements ProcessorInterface
{
    private Security $security;
    private PersistProcessor $persistProcessor;
    private UserPasswordHasherInterface $hasher;
    private JWTTokenManagerInterface $jWTToken;

    public function __construct(
        Security $security,
        UserPasswordHasherInterface $hasher,
        JWTTokenManagerInterface $jWTToken,
        PersistProcessor $persistProcessor
    ) {
        $this->security = $security;
        $this->persistProcessor = $persistProcessor;
        $this->hasher = $hasher;
        $this->jWTToken = $jWTToken;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var User $auth */
        $auth = $this->security->getUser();

        $auth->setPassword(
            $this->hasher->hashPassword($auth, $data->getConfirmationPassword())
        );

        $this->persistProcessor->process($auth, $operation, $uriVariables, $context);

        $auth->eraseCredentials();

        return new JsonResponse(["token", $this->jWTToken->create($auth)]);
    }
}
