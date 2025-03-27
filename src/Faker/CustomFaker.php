<?php

namespace App\Faker;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;

class CustomFaker
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher){
        $this->hasher = $hasher;
    }

    public function hashPassword(string $plainPassword): string {

        return $this->hasher->hashPassword(new User, $plainPassword);
    }
}