<?php

namespace App\Tests\src\EventListener;

use App\Traits\FixturesTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use App\Entity\User;
use App\EventListener\JWTAuthenticationSuccessListener;
use App\Repository\NotificationRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\HttpFoundation\Response;

class JWTAuthenticationSuccessListenerTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();

        $this->loadFixturesTrait();
    }

    public function testOnAuthenticationSuccess(): void
    {
        /** @var User */
        $user = $this->all_fixtures['user_adm_society'];
        /** @var AuthenticationSuccessEvent */
        $authenticationSuccessEvent = new AuthenticationSuccessEvent([], $user, new Response());

        $notificationRepository = $this->getContainer()->get(NotificationRepository::class);
        //dispatch
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(AuthenticationSuccessEvent::class, [new JWTAuthenticationSuccessListener($notificationRepository), 'onAuthenticationSuccess']);
        $dispatcher->dispatch($authenticationSuccessEvent, AuthenticationSuccessEvent::class);

        $this->assertCount(1, $authenticationSuccessEvent->getData()['notifications']);
        $this->assertEquals($user->getId(), $authenticationSuccessEvent->getData()['notifications'][0]->getUser()->getId());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
