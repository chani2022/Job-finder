<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: User::class)]
final class UserListener
{

    public function prePersist(User $user, PrePersistEventArgs $event): void
    {
        $object = $event->getObject();
        if (!$object instanceof User) return;
        $object->setStatus(false);
    }
}
