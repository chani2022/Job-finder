<?php

namespace App\State\Society;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Society;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;
use App\Service\PaymentService;
use Doctrine\ORM\EntityManagerInterface;

class SocietyPostProcessor implements ProcessorInterface
{
    private Security $security;
    private EntityManagerInterface $em;
    private PaymentService $payment;

    public function __construct(Security $security, EntityManagerInterface $em, PaymentService $payement)
    {
        $this->security = $security;
        $this->em = $em;
        $this->payment = $payement;
    }
    /**
     * @param Society $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?Society
    {
        $user = $this->security->getUser();

        $this->payment->prepare();

        /** @var User $user */
        $user = $this->em->getRepository(User::class)->find($user->getId());
        $user->setRoles(['ROLE_ADMIN'])
            ->setSociety($data);

        $data->addUser($user);

        $this->em->persist($data);
        $this->em->flush();

        return $data;
    }
}
