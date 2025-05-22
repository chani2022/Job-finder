<?php

namespace App\Tests\src\Entity;

use App\Entity\OffreEmploi;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OffreEmploiTest extends KernelTestCase
{
    private ValidatorInterface|MockObject|null $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = $this->getContainer()->get(ValidatorInterface::class);
    }

    /**
     * @dataProvider dataValidBlank
     */
    public function testOffreEmploiValidator(string $titre, string $description, string $indice): void
    {
        $offreEmploi = (new OffreEmploi())
            ->setTitre($titre)
            ->setDescription($description);

        $errors = $this->validator->validate($offreEmploi, null, ['post:create:validator']);
        $excepted = 0;
        if ($indice == 'titre_valid' || $indice == 'description_valid') {
            $excepted = 1;
        } else if ($indice == "titre_description_invalid") {
            $excepted = 2;
        }
        $this->assertCount($excepted, $errors);
    }

    public static function dataValidBlank(): array
    {
        return [
            'titre_description_valid' => ['titre' => 'Mon titre', 'description' => 'mon description', 'indice' => 'titre_description_valid'],
            'titre_valid' => ['titre' => 'Mon titre', 'description' => '', 'indice' => 'titre_valid'],
            'description_valid' => ['titre' => '', 'description' => 'mon description', 'indice' => 'description_valid'],
            'titre_description_invalid' => ['titre' => '', 'description' => '', 'indice' => 'titre_description_invalid'],
        ];
    }


    protected function tearDown(): void
    {
        parent::tearDown();

        $this->validator = null;
    }
}
