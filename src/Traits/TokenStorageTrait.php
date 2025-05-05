<?php

namespace App\Traits;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

trait TokenStorageTrait
{
    protected TokenStorageInterface $tokenStorage;

    public function logUser(User $user)
    {
        $token = $this->createMock(TokenInterface::class);
        $token
            ->method('setUser')
            ->with($user);

        $token->method('getUser')
            ->willReturn($user);

        $token->setUser($user);

        /** @var TokenStorageInterface */
        $this->tokenStorage = static::getContainer()->get(TokenStorageInterface::class);
        $this->tokenStorage->setToken($token);
    }
}
