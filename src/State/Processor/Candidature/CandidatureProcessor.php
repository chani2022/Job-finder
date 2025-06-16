<?php

namespace App\State\Processor\Candidature;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use App\Entity\Candidature;
use App\Entity\MediaObject;
use App\Entity\OffreEmploi;
use App\Entity\PieceJointe;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CandidatureProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private ValidatorInterface $validator,
        private readonly TokenStorageInterface $tokenStorage,
        private UserProviderInterface $userProvider
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Candidature|ValidatorException
    {
        $user = $this->getAuthenticatedUser();
        $candidature = new Candidature();

        if ($this->checkKeyRequestFromContext($context)) {
            if ($this->checkKeysRequest($context['request'])) {
                $data = $this->getDataRequest($context['request']);

                $lettre = $data['lettre'];
                $id_offreEmploi = $data['id_offre'];
                $file = $data['file'];

                $candidature = $this->setCandidature($user, $id_offreEmploi, $lettre, $file);
                $this->validate($candidature);

                $this->save($candidature);
            }
        }

        return $candidature;
    }
    /** 
     * @return User|NotFoundHttpException
     */
    public function getAuthenticatedUser()
    {
        $token = $this->tokenStorage->getToken();

        if (!$token || !($user = $token->getUser()) instanceof User) {
            throw new LogicException('Aucun utilisateur authentifié.');
        }

        return $this->userProvider->refreshUser($user);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function checkKeyRequestFromContext(array $context): bool
    {
        if (array_key_exists('request', $context)) {
            return true;
        }
        throw new InvalidArgumentException('la clé request n\'est pas dans la request');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function checkKeysRequest(Request $request): bool
    {
        $KeysValid = ['id_offre', 'lettre', 'file'];
        foreach ($KeysValid as $key) {
            $hasKey = $key === 'file'
                ? $request->files->has($key)
                : $request->request->has($key);

            if (!$hasKey) {
                throw new InvalidArgumentException("La clé obligatoire \"$key\" est manquante.");
            }
        }
        return true;
    }

    /**
     * @throws ValidatorException
     */
    public function validate(Candidature $candidature): bool
    {
        $errors = $this->validator->validate($candidature, null, ['groups' => 'post:validator']);
        if ($errors->count() > 0) {
            throw new ValidatorException($errors);
        }
        return true;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getDataRequest(Request $request): array
    {
        $id_offre = $request->request->get('id_offre');
        $lettre = $request->request->get('lettre');
        $file = $request->files->get('file');

        if (is_null($id_offre) || is_null($lettre)) {
            throw new InvalidArgumentException('Paramètre manquants');
        }
        return [
            'id_offre' => $id_offre,
            'lettre' => $lettre,
            'file' => $file
        ];
    }

    public function setCandidature(User $user, string $id_offreEmploi, string $lettre, UploadedFile $file): Candidature
    {
        $media = new MediaObject();
        $media->file = $file;

        $pieceJointe = (new PieceJointe())
            ->setLettreMotivation($lettre)
            ->setOwner($user)
            ->setCv($media);

        $offreEmploi = $this->em
            ->getRepository(OffreEmploi::class)
            ->find($id_offreEmploi);

        return (new Candidature())
            ->setCandidat($user)
            ->setOffreEmploi($offreEmploi)
            ->setPieceJointe($pieceJointe);
    }

    public function save(Candidature $candidature): void
    {
        $this->em->persist($candidature);
        $this->em->flush();
    }
}
