<?php

namespace App\EventListener;
// src/App/EventListener/JWTCreatedListener.php

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use App\Entity\User;
use Vich\UploaderBundle\Storage\StorageInterface;

class JWTCreatedListener
{
    public function __construct(
        private readonly StorageInterface $storage
    ) {}
    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        /** @var User */
        $user = $event->getUser();

        $payload = $event->getData();

        $payload['nom'] = $user->getNom();
        $payload['prenom'] = $user->getPrenom();
        $payload['password'] = $user->getPassword();
        $payload['email'] = $user->getEmail();
        $payload['username'] = $user->getUsername();

        $payload['image'] = $user->image ? $this->storage->resolveUri($user->image, 'file') : null;

        $event->setData($payload);
    }
}
