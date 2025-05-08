<?php

namespace App\MeilSearch;

use App\Repository\UserRepository;
use Meilisearch\Client;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class MeilSearchService
{
    private Client $client;
    private Serializer $serializer;


    public function __construct(
        private readonly string $meili_url,
        private readonly string $meili_key,
        private UserRepository $userRepository
    ) {
        $this->client = new Client($meili_key, $meili_key);
        $this->serializer = new Serializer([new ObjectNormalizer()], [], [
            AbstractNormalizer::GROUPS => ["read:user:get", "read:user:collection"]
        ]);
    }

    public function populateUser()
    {
        $index = $this->client->index('app_dev_user');
        $users = $this->userRepository->findAll();

        $index->addDocuments($users);
    }
}
