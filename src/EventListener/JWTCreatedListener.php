<?php

namespace App\EventListener;
// src/App/EventListener/JWTCreatedListener.php

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use App\Entity\User;

class JWTCreatedListener
{

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

        $event->setData($payload);
    }
}
