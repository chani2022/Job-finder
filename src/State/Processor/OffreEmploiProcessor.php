<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\OffreEmploi;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class OffreEmploiProcessor implements ProcessorInterface
{
    public function __construct(private TokenStorageInterface $tokenStorage, private EntityManagerInterface $em) {}
    /**
     * @param OffreEmploi $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        $user = $this->tokenStorage->getToken()?->getUser();
        if (is_null($user)) {
            throw new UnauthorizedHttpException(challenge: 'test');
        }
        if ($operation instanceof Post) {
            if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles()) || !in_array('ROLE_ADMIN', $user->getRoles())) {
                throw new UnauthorizedHttpException(challenge: 'test');
            }
            $data->setUser($this->tokenStorage->getToken()->getUser())
                ->setDateCreatedAt(new DateTimeImmutable());

            $this->em->persist($data);
            $this->em->flush();
        }

        return $data;
    }
}
