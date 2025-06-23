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
use App\Mailer\ServiceMailer;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserProvider;
use LogicException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CandidatureProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private ValidatorInterface $validator,
        private readonly TokenStorageInterface $tokenStorage,
        private JWTUserProvider $userProvider,
        private readonly ServiceMailer $serviceMailer
    ) {}
    /**
     * @throws ValidatorException
     * @throws LogicException
     * @throws NotFoundException
     * @throws InvalidArgumentException
     */
    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ) {
        $user = $this->getAuthenticatedUser();

        $this->checkKeyRequestFromContext($context);
        $this->checkKeysRequest($context['request']);
        $data = $this->getDataRequest($context['request']);

        $lettre = $data['lettre'];
        $id_offreEmploi = $data['id_offre'];
        $file = $data['file'];

        if (!$file instanceof UploadedFile) {
            return;
        }

        $candidature = $this->setCandidature($user, $id_offreEmploi, $lettre, $file);

        $this->validate($candidature);

        $this->save($candidature);

        //envoye d'email
        $filename = $candidature->getPieceJointe()->getCv()->filePath;
        $name = $user->getNom() ? $user->getNom() . ' ' . $user->getPrenom() : $user->getEmail();
        $this->serviceMailer
            ->to($candidature->getOffreEmploi()->getUser()->getEmail())
            ->from($user->getEmail())
            ->htmlTemplate('emails/candidature.html.twig')
            ->attachFile($filename, $name);

        return $candidature;
    }
    /** 
     * @throws LogicException si utilisateur n'est présent ou ne pas une instance of de user
     */
    public function getAuthenticatedUser(): User
    {
        $token = $this->tokenStorage->getToken();

        if (!$token || !($user = $token->getUser()) instanceof User) {
            throw new LogicException('Aucun utilisateur authentifié.');
        }

        return $this->userProvider->refreshUser($user);
    }

    /**
     * @throws InvalidArgumentException si la clé request n'est pas présent dans le context
     */
    public function checkKeyRequestFromContext(array $context): bool
    {
        if (array_key_exists('request', $context)) {
            return true;
        }
        throw new InvalidArgumentException('la clé request n\'est pas dans la request');
    }

    /**
     * @throws InvalidArgumentException si ces clés ne sont pas present dans le request id_offre, lettre, file
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
     * @throws ValidatorException si la constraintValidatorList contient une erreur
     */
    public function validate(Candidature $candidature)
    {
        $errors = $this->validator->validate($candidature, null, ['groups' => 'post:validator']);
        if ($errors->count() > 0) {
            throw new ValidatorException($errors);
        }
    }

    /**
     * @throws InvalidArgumentException si les paramètres sont manquants ou n'est pas renseignés
     * @return array<string, string|File>
     */
    public function getDataRequest(Request $request)
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

    /**
     * @throws NotFoundHttpException si l'objet offre emploi est introuvable
     */
    public function setCandidature(User $user, string $id_offreEmploi, string $lettre, UploadedFile $file): Candidature
    {
        $offreEmploi = $this->em
            ->getRepository(OffreEmploi::class)
            ->find($id_offreEmploi);

        if (!$offreEmploi) {
            throw new NotFoundHttpException(sprintf('L\'offre emploi %d est introuvable!', $id_offreEmploi));
        }

        $filename = $file->getClientOriginalName();
        $media = new MediaObject();
        $media->file = $file;
        $media->filePath = $filename;


        $pieceJointe = (new PieceJointe())
            ->setLettreMotivation($lettre)
            ->setOwner($user)
            ->setCv($media);

        return (new Candidature())
            ->setCandidat($user)
            ->setOffreEmploi($offreEmploi)
            ->setPieceJointe($pieceJointe);
    }
    /**
     * permet de sauvegarde dans la bdd
     */
    public function save(Candidature $candidature): void
    {
        $this->em->persist($candidature);
        $this->em->flush();
    }
}
