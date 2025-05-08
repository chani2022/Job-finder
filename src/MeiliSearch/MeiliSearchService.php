<?php

namespace App\MeiliSearch;

use Meilisearch\Client;

class MeiliSearchService
{
    protected Client $client;

    /**
     * @param string $meili_url
     * @param string $meili_key
     * @param string $meili_prefix
     */
    public function __construct(
        private readonly string $meili_url,
        private readonly string $meili_key,
        private readonly string $meili_prefix
    ) {
        $this->client = new Client($meili_url, $meili_key);
    }
    /**
     * @param string $index_name    nom de l'index de la recherche qui est definit dans la config meilisearch e.x: user
     * @param string|null $query
     * @return array
     */
    public function search(string $index_name, ?string $query = null): array
    {
        $index = $this->client->index($this->meili_prefix . '' . $index_name);
        return $index->search($query, [
            'attributesToHighlight' => ['*'],
            'highlightPreTag' => '<em>',
            'highlightPostTag' => '</em>'
        ])->getRaw();
    }
}
