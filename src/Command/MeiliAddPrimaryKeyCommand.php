<?php

namespace App\Command;

use Meilisearch\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(
    name: 'meilisearch:add-primaryKey',
    description: 'Ajouter des clé primaire au index.',
)]
class MeiliAddPrimaryKeyCommand extends Command
{

    public function __construct(
        private readonly Client $meili,
    ) {
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $indexes = $this->meili->getIndexes();
        $indexes = array_map(fn($index) => $index->getUid(), $indexes->getResults());
        $writeln = '';
        foreach ($indexes as $indexName) {
            $this->meili->createIndex($indexName, ['primaryKey' => 'id']);
            $writeln .= "✅ Index <info>$indexName</info> créé avec primaryKey 'id'\n";
        }
        $output->writeln($writeln);
        return Command::SUCCESS;
    }
}
