<?php

namespace App\State\Provider\User;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\MeiliSearch\MeiliSearchService;
use App\Repository\UserRepository;

class UserProvider implements ProviderInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private MeiliSearchService $meiliSearchService
    ) {}
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            $this->meiliSearchService->setIndexName('user');
            $this->meiliSearchService->setOptions(['sort' => ['id:desc']]);
            return $this->meiliSearchService->search();
        }
        return $this->userRepository->find($uriVariables['id']);
    }
}
