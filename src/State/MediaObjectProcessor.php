<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\MediaObject;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MediaObjectProcessor implements ProcessorInterface
{

    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
    ) {}
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $data = $context['request']->request->all();
        $file = $context['request']->files->all()["file"];
        $media_object = new MediaObject();
        $media_object->file = $file;

        $this->persistProcessor->process($media_object, $operation, $uriVariables, $context);

        return $media_object;
    }
}
