<?php

namespace App\Tests\src\State\Society;

use ApiPlatform\Metadata\Post;
use App\Entity\Society;
use App\State\Society\SocietyPostProcessor;
use App\Traits\FixturesTrait;
use App\Traits\LogUserTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class SocietyPostProcessorTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;
    use LogUserTrait;

    private ?Security $security;
    private ?EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->security = static::getContainer()->get(Security::class);
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $this->loadFixturesTrait();
    }

    public function testSocietyProcessor(): void
    {
        /** @var User $user */
        $user = $this->all_fixtures['user_1'];
        $this->logUserTrait($user);

        $data = new Society();
        $data->setNomSociety("test");
        $post = new Post();

        $societyProcessor = new SocietyPostProcessor($this->security, $this->em);

        $data = $societyProcessor->process($data, $post, [], []);

        /** @var Serializer $serializer */
        $serializer = static::getContainer()->get(SerializerInterface::class);
        $json = $serializer->serialize($data, 'json');
        $responseData = json_decode($json, true);

        // $responseData = $response->toArray();

        $this->assertArrayHasKey('users', $responseData);
        $this->assertIsArray($responseData['users']);
        $this->assertEquals("TEST", $responseData['nom_society']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->security = null;
        $this->em = null;
    }
}
