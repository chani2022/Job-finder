<?php

namespace App\State\OffreEmploi;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\MeiliSearch\MeiliSearchService;

class OffreEmploiProvider implements ProviderInterface
{
    public function __construct(private readonly MeiliSearchService $meiliSearchService) {}
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!$operation instanceof GetCollection) return [];

        $query = $context['request']->query->get('query');

        $this->meiliSearchService->setIndexName('offreEmploi');
        $this->meiliSearchService->setOptions([
            'sort' => ['date_created_at:desc', 'id:desc'],
            'attributesToCrop' => ['description'],
            'cropLength' => 30
        ]);

        return $this->meiliSearchService->search($query);
    }
}
