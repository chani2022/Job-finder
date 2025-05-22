<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\OffreEmploi;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class OffreEmploiProcessor implements ProcessorInterface
{
    public function __construct(private TokenStorageInterface $tokenStorage, private EntityManagerInterface $em) {}
    /**
     * @param OffreEmploi $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): OffreEmploi
    {
        /** @var User|null */
        $user = $this->tokenStorage->getToken()?->getUser();
        if (is_null($user)) {
            throw new UnauthorizedHttpException(challenge: 'test');
        }
        if ($operation instanceof Post) {
            /**
             * on récupère l'entity depuis doctrine, 
             * pour eviter d'activer l'insertion de nouvelle user dans la table
             */
            $user = $this->em->getRepository(User::class)->find($user->getId());

            if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles()) && !in_array('ROLE_ADMIN', $user->getRoles())) {
                throw new UnauthorizedHttpException(challenge: 'test');
            }
            $data->setUser($user)
                ->setDateCreatedAt(new DateTimeImmutable());

            $this->em->persist($data);
            $this->em->flush();
        }

        return $data;
    }
}
