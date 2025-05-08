<?php

namespace App\State\Provider\User;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\MeiliSearch\MeiliSearchService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class UserProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $itemProvider,
        private MeiliSearchService $meiliSearchService
    ) {}
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            return $this->meiliSearchService->search('user');
        }

        return $this->itemProvider->provide($operation, $uriVariables, $context);
    }
}
