<?php

namespace App\Tests\src\EventListener;

use App\Entity\User;
use App\EventListener\JWTCreatedListener;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class JWTCreatedListenerTest extends TestCase
{
    public function testJwtCreatedListener(): void
    {

        $data = [
            "nom" => "test",
            "prenom" => "test",
            "password" => "test",
            "email" => "test@test.com",
            "username" => "test"
        ];
        $user = (new User())
            ->setNom("test")
            ->setPrenom("test")
            ->setEmail("test@test.com")
            ->setUsername("test")
            ->setPassword("test");

        $jwtCreatedEvent = new JWTCreatedEvent($data, $user);
        $jwtCreatedListener = new JWTCreatedListener();

        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(JWTCreatedEvent::class, [$jwtCreatedListener, 'onJWTCreated']);

        $dispatcher->dispatch($jwtCreatedEvent, JWTCreatedEvent::class);

        $this->assertEquals($data, $jwtCreatedEvent->getData());
    }
}
