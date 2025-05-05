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
use App\Service\PaymentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use PHPUnit\Framework\MockObject\MockObject;

class SocietyPostProcessorTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;
    use LogUserTrait;

    private ?Security $security;
    private ?EntityManagerInterface $em;
    /** @var MockObject|PaymentService|null $payment */
    private $payment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->security = static::getContainer()->get(Security::class);
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->payment = $this->createMock(PaymentService::class);

        $this->loadFixturesTrait();
    }

    public function testSocietyPostProcessor(): void
    {
        /** @var User $user */
        $user = $this->all_fixtures['user_1'];
        $this->logUserTrait($user);

        $data = new Society();
        $data->setNomSociety("test");

        $post = new Post();

        $societyProcessor = new SocietyPostProcessor($this->security, $this->em);

        /**  @var Society $society */
        $society = $societyProcessor->process($data, $post, [], []);

        /** @var Society $data_bdd */
        $data_bdd = $this->em->getRepository(Society::class)->findOneBy([
            "nom_society" => $data->getNomSociety()
        ]);
        //assert data in bdd
        $this->assertEquals($data_bdd->getId(), $society->getId());
        $this->assertFalse($data_bdd->isStatus(), $society->isStatus());

        $this->assertCount(1, $data->getUsers());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->security = null;
        $this->em = null;
    }
}
