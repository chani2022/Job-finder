<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class UserActiveStatusController extends AbstractController
{
    #[Route('/user/active/status/{id}', name: 'app_active_status', methods: ['GET'])]
    public function active(User $user, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): Response
    {
        $user->setStatus(true);

        $em->flush();

        return new RedirectResponse("/api");
    }
}
