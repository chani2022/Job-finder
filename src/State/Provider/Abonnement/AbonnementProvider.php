<?php

namespace App\State\Provider\Abonnement;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\AbonnementRepository;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AbonnementProvider implements ProviderInterface
{
    public function __construct(private TokenStorageInterface $token, private AbonnementRepository $abonnementRepository) {}
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (is_null($this->token->getToken())) {
            throw new UnauthorizedHttpException(challenge: 'test');
        }
        if ($operation instanceof CollectionOperationInterface) {
            if (in_array("ROLE_SUPER_ADMIN", $this->token->getToken()->getUser()->getRoles())) {
                return $this->abonnementRepository->findAllAbonnements();
            } else {
                return $this->abonnementRepository->findAbonnementsOwner($this->token->getToken()->getUser());
            }
        }
        return null;
    }
}
