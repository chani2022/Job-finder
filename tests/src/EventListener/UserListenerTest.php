<?php

namespace App\Tests\src\EventListener;

use App\Entity\User;
use App\EventListener\UserListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use PHPUnit\Framework\TestCase;

class UserListenerTest extends TestCase
{
    public function testPrePersist(): void
    {
        $objectManager = $this->createMock(EntityManagerInterface::class);
        $user = new User();
        $prePersist = new PrePersistEventArgs($user, $objectManager);
        $userListener = new UserListener();
        $userListener->prePersist($user, $prePersist);

        $this->assertTrue($user->isStatus());
    }
}
