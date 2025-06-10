<?php

namespace App\State\OffreEmploi;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\OffreEmploi;
use App\MeiliSearch\MeiliSearchService;

class OffreEmploiProvider implements ProviderInterface
{
    private function __construct(private readonly MeiliSearchService $meiliSearchService) {}
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof GetCollection && $context['resource_class'] instanceof OffreEmploi) {
            $query = $context['request']->query->get('query');

            $this->meiliSearchService->setIndexName('offreEmploi');
            return $this->meiliSearchService->search($query);
        }
        return [];
    }
}
