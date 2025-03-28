<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProfilUserProcessor implements ProcessorInterface
{
    private JWTTokenManagerInterface $jWTTokenManager;
    private Security $security;
    private EntityManagerInterface $em;

    public function __construct(JWTTokenManagerInterface $jWTTokenManager, Security $security, PersistProcessor $persistProcessor, EntityManagerInterface $em)
    {
        $this->jWTTokenManager = $jWTTokenManager;
        $this->security = $security;
        $this->em = $em;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var User $user */
        $user = $this->security->getUser();

        // // pour que FLUSH $em declenche, si non il ne fait rien
        $user = $this->em->getRepository(User::class)->find($user->getId());

        $data = $context['request']->toArray();
        foreach ($data as $attr => $value) {
            $method = 'set' . ucfirst($attr);
            if (method_exists($user, $method)) {
                call_user_func([$user, $method], $value);
            }
        }

        $this->em->flush();

        return new JsonResponse(["token" => $this->jWTTokenManager->create($user)]);
    }
}
