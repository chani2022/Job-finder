<?php

namespace App\EventListener;

use App\Entity\User;
use App\Repository\NotificationRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class JWTAuthenticationSuccessListener
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository
    ) {}
    /**
     * @param AuthenticationSuccessEvent $event
     *
     * @return void
     */
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event)
    {
        /** @var User */
        $user = $event->getUser();
        $data = $event->getData();

        $notifications = $this->notificationRepository->findBy(['user' => $user]);

        $data['notifications'] = $notifications;
        $event->setData($data);
    }
}
