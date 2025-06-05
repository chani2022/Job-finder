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
    protected array $options;

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
        $this->options = [
            'attributesToHighlight' => ['*'],
            'highlightPreTag' => '<em>',
            'highlightPostTag' => '</em>'
        ];
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

        $this->applySortable();
        $index = $this->client->index($this->meili_prefix . '' . $this->index_name);
        return $index->search($query, $this->options)->getRaw();
    }

    public function getIndexNames(): array
    {
        /** @var IndexesResults */
        $indexes = $this->client->getIndexes();

        return array_map(fn($index) => $index->getUid(), $indexes->getResults());
    }

    public function getIndexName(): string
    {
        return $this->index_name;
    }
    /**
     * @return void|InvalidArgumentException
     */
    public function setIndexName(string $index_name)
    {
        if ($this->checkIndexName($index_name)['is_index_name_valid']) {
            $this->index_name = $index_name;
        } else {
            throw new InvalidArgumentException('Les index valide sont : ' . trim($this->checkIndexName($index_name)['list_index_valid'], ', '));
        }
    }

    public function checkIndexName(string $index_name): array
    {
        $index_valid = false;
        $str_index = '';
        $indexes = $this->getIndexNames();
        //liste les noms de l'index
        foreach ($indexes as $index) {
            $array_index = explode('_', $index);
            $str_index .= $array_index[count($array_index) - 1] . ', ';

            if (str_contains($index, $index_name)) {
                $index_valid = true;
            }
        }

        return [
            'is_index_name_valid' => $index_valid,
            'list_index_valid' => $str_index
        ];
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): self
    {
        foreach ($options as $key => $option) {
            $this->options[$key] = $option;
        }

        return $this;
    }

    protected function applySortable(): void
    {
        switch ($this->index_name) {
            case 'user':
                $this->setOptions(['sort' => ['id:desc']]);
                break;
            case 'offreEmploi':
                $this->setOptions(['sort' => ['date_created_at:desc']]);
                break;
            default:
                break;
        }
    }
}
