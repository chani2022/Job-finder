<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Entity\MediaObject;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ProfilUserProcessor implements ProcessorInterface
{
    private JWTTokenManagerInterface $jWTTokenManager;
    private Security $security;
    private EntityManagerInterface $em;
    private ValidatorInterface $validator;

    public function __construct(JWTTokenManagerInterface $jWTTokenManager, Security $security, EntityManagerInterface $em, ValidatorInterface $validator)
    {
        $this->jWTTokenManager = $jWTTokenManager;
        $this->security = $security;
        $this->em = $em;
        $this->validator = $validator;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var User $user */
        $user = $this->security->getUser();

        /** @var User $user */
        // Recharge l'entité User depuis Doctrine pour que le flush fonctionne
        $user = $this->em->getRepository(User::class)->find($user->getId());

        $data = $context['request']->request->all();
        $file = $context['request']->files->get('file');

        if ($file) {
            $media = new MediaObject();
            $media->file = $file;

            $user->image = $media;
        }

        $user = $this->setProperties($user, $data);

        /** @var ConstraintViolationListInterface $errors */
        $errors = $this->validator->validate($user, null, ["profil:validator"]);
        if ($errors->count() > 0) {
            throw new ValidationException($errors);
        }

        $this->em->flush();

        return new JsonResponse(["token" => $this->jWTTokenManager->create($user)]);
    }

    private function setProperties(User $user, array $data): ?User
    {
        foreach ($data as $attr => $value) {
            $method = 'set' . ucfirst($attr);
            if (method_exists($user, $method)) {
                $v = $value;
                if ($attr == "nom") {
                    $v = strtoupper($v);
                } else if ($attr == "prenom") {
                    $v = ucwords($v);
                }
                call_user_func([$user, $method], $v);
            }
        }

        return $user;
    }
}
