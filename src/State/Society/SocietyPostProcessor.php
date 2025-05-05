<?php

namespace App\State\Society;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Society;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class SocietyPostProcessor implements ProcessorInterface
{
    private Security $security;
    private EntityManagerInterface $em;

    public function __construct(Security $security, EntityManagerInterface $em)
    {
        $this->security = $security;
        $this->em = $em;
    }
    /**
     * @param Society $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?Society
    {
        $user = $this->security->getUser();
        /** @var User $user */
        $user = $this->em->getRepository(User::class)->find($user->getId());
        $user->setSociety($data);

        $data->addUser($user);

        $this->em->persist($data);
        $this->em->flush();

        return $data;
    }
}
