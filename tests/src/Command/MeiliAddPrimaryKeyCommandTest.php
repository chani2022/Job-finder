<?php

namespace App\Tests\src\Command;

use App\Command\MeiliAddPrimaryKeyCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class MeiliAddPrimaryKeyCommandTest extends KernelTestCase
{
    public function testExecuteMeili(): void
    {
        self::bootKernel();

        $application = new Application();
        $command = self::getContainer()->get(MeiliAddPrimaryKeyCommand::class);
        $application->add($command);

        $commandTester = new CommandTester($application->find('meilisearch:add-primaryKey'));
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString("✅ Index", $output);
        $this->assertEquals(0, $commandTester->getStatusCode());
    }
}
