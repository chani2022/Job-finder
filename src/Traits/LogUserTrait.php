<?php

namespace App\Traits;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

trait LogUserTrait
{

    public function logUserTrait(User $user)
    {
        $tokenManager = static::getContainer()->get(TokenStorageInterface::class);
        $token = new UsernamePasswordToken($user, 'api', $user->getRoles());
        $tokenManager->setToken($token);
    }
}
