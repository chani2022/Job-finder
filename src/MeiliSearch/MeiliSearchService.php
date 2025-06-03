<?php

namespace App\MeiliSearch;

use Exception;
use Meilisearch\Client;
use Meilisearch\Contracts\IndexesResults;
use Meilisearch\Exceptions\InvalidArgumentException;

class MeiliSearchService
{
    protected Client $client;
    protected ?string $index_name;

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
        $this->index_name = null;
    }

    /**
     * @param string $index_name   nom de l'index de la recherche qui est definit dans la config meilisearch e.x: user
     * @param string|null $query
     * @return array
     */
    public function search(?string $query = null): array
    {
        if (is_null($this->index_name)) {
            throw new Exception("Vous devez appeler la methode setIndexName avant de faire la recherche sur meili");
        }
        $index = $this->client->index($this->meili_prefix . '' . $this->index_name);
        return $index->search($query, [
            'attributesToHighlight' => ['*'],
            'highlightPreTag' => '<em>',
            'highlightPostTag' => '</em>'
        ])->getRaw();
    }

    public function getIndexNames(): array
    {
        /** @var IndexesResults */
        $indexes = $this->client->getIndexes();

        return array_map(fn($index) => $index->getUid(), $indexes->getResults());
    }
    /**
     * @return void|InvalidArgumentException
     */
    public function setIndexName(string $index_name)
    {
        $indexes = $this->getIndexNames();
        $str_index = '';
        $index_valid = false;
        foreach ($indexes as $index) {
            //liste les noms de l'index
            $array_index = explode('_', $index);
            $str_index .= $array_index[count($array_index) - 1] . ', ';

            if (str_contains($index, $index_name)) {
                $index_valid = true;
            }
        }

        if ($index_valid) {
            $this->index_name = $index_name;
        } else {
            throw new InvalidArgumentException('Les index valide sont : ' . trim($str_index, ', '));
        }
    }

    public function getIndexName(): string
    {
        return $this->index_name;
    }
}
