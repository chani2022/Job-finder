<?php

namespace App\State\Processor\Candidature;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Entity\Candidature;
use App\Entity\MediaObject;
use App\Entity\OffreEmploi;
use App\Entity\PieceJointe;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CandidatureProcessor implements ProcessorInterface
{
    public function __construct(private readonly EntityManagerInterface $em, private ValidatorInterface $validator, private readonly TokenStorageInterface $tokenStorage) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Candidature|ValidationException
    {
        /** @var User  */
        $user = $this->tokenStorage->getToken()->getUser();
        $user = $this->em->getRepository(User::class)->find($user->getId());

        $request = $context['request'];
        $file = $request->files->get('file');
        $id_offreEmploi = $request->request->get('id_offre');
        $lettre = $request->request->get('lettre');

        $offreEmploi = $this->em->getRepository(OffreEmploi::class)->find($id_offreEmploi);

        $candidature = new Candidature();
        $candidature->setCandidat($user)
            ->setOffreEmploi($offreEmploi);

        $media = new MediaObject();
        $media->file = $file;

        $pieceJointe = (new PieceJointe())
            ->setLettreMotivation($lettre)
            ->setOwner($user)
            ->setCv($media);

        $candidature->setPieceJointe($pieceJointe);

        $errors = $this->validator->validate($candidature, null, ['groups' => 'post:validator']);
        if ($errors->count() > 0) {
            throw new ValidationException($errors);
        }

        $this->em->persist($candidature);
        $this->em->flush();

        return $candidature;
    }
}
