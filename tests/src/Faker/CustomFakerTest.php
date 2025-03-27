<?php

namespace App\Tests\src\Faker;

use App\Faker\CustomFaker;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CustomFakerTest extends KernelTestCase
{
    public function testHashPassword(): void
    {
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $customFaker = new CustomFaker($hasher);
        $this->assertStringContainsString("$", $customFaker->hashPassword("test"));
    }
}
