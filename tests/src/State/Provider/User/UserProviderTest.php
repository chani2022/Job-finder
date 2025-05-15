<?php

namespace App\Tests\src\State\Provider\User;

use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use App\MeiliSearch\MeiliSearchService;
use App\State\Provider\User\UserProvider;
use App\Traits\FixturesTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\User;
use App\Repository\UserRepository;

class UserProviderTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;

    private ?UserRepository $userRepository;
    private ?MeiliSearchService $meiliSearchService;

    protected function setUp(): void
    {
        parent::setUp();
        $container = static::getContainer();
        $this->userRepository = $container->get(UserRepository::class);
        $this->meiliSearchService = $container->get(MeiliSearchService::class);
        $this->loadFixturesTrait();
    }

    /**
     * @dataProvider getData
     */
    public function testProvideUser(Operation|string $operation, array $uriVariables, array $context): void
    {
        /** @var User */
        $user = $this->all_fixtures['user_1'];
        if (!$operation instanceof CollectionOperationInterface) {
            $uriVariables['id'] = $user->getId();
        }

        $userProvider = new UserProvider($this->userRepository, $this->meiliSearchService);

        $data = $userProvider->provide($operation, $uriVariables, $context);

        if (!$operation instanceof CollectionOperationInterface) {
            $this->assertEquals($data->getId(), $user->getId());
        } else {
            $this->assertArrayHasKey("hits", $data);
            $this->assertGreaterThan(2, $data['nbHits']);
        }
    }

    public static function getData(): array
    {
        return [
            "collection_operation" => ["operation" => new GetCollection(), "uriVariables" => [], "context" => []],
            "item_operation" => ["operation" => new Get(), "uriVariables" => [], "context" => []]
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->userRepository = null;
        $this->meiliSearchService = null;
    }
}
