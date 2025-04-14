<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;

class DisabledUserProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private EntityManagerInterface $em
    ) {}
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $user_to_disabled = $this->em->getRepository(User::class)->find($uriVariables['id']);
        $user_to_disabled->setStatus(false);
        $this->persistProcessor->process($user_to_disabled, $operation, $uriVariables, $context);
        return new JsonResponse(["user" => $user_to_disabled]);
    }
}
