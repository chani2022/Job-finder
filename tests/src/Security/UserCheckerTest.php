<?php

namespace App\Tests\src\Security;

use App\Entity\User;
use App\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserCheckerTest extends TestCase
{
    public function testCheckPreAuth(): void
    {
        $this->expectException(CustomUserMessageAccountStatusException::class);
        $this->expectExceptionMessage("Votre compte est désactivé.");

        $user = (new User())
            ->setStatus(false);

        $userChecker = new UserChecker();
        $userChecker->checkPreAuth($user);
    }
}
