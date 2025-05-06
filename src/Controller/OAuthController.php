<?php

namespace App\Controller;

use App\Entity\MediaObject;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class OAuthController extends AbstractController
{
    private EntityManagerInterface $em;
    private JWTTokenManagerInterface $jWTTokenManager;
    private UserPasswordHasherInterface $hasher;
    private string $redirect_url_front;

    public const DEFAULT_PLAIN_PASSWORD = "offre";

    public function __construct(EntityManagerInterface $em, JWTTokenManagerInterface $jWTTokenManager, UserPasswordHasherInterface $hasher, string $redirect_url_front)
    {
        $this->em = $em;
        $this->jWTTokenManager = $jWTTokenManager;
        $this->hasher = $hasher;
        $this->redirect_url_front = $redirect_url_front;
    }

    #[Route('/connect/{oauth}', name: 'connect_oauth_start')]
    public function connectAction(string $oauth, ClientRegistry $clientRegistry): RedirectResponse
    {

        $scope = [
            'profile',
            'email' // the scopes you want to access
        ];
        switch ($oauth) {
            case 'facebook':
                $scope = ['email', 'public_profile'];
                break;
        }

        return $clientRegistry
            ->getClient($oauth) // key used in config/packages/knpu_oauth2_client.yaml
            ->redirect($scope, []);
    }

    /**
     * After going to Facebook, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml
     */
    #[Route("/connect/oauth/check", name: "connect_oauth_check")]
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry)
    {

        $client_oauth = $request->query->get('client');
        // ** if you want to *authenticate* the user, then
        // leave this method blank and create a Guard authenticator
        // (read below)

        /** @var \KnpU\OAuth2ClientBundle\Client\Provider\FacebookClient $client */
        $client = $clientRegistry->getClient($client_oauth);

        // try {
        // the exact class depends on which provider you're using
        /** @var \League\OAuth2\Client\Provider\FacebookUser $user */
        $user_social = $client->fetchUser();
        $data = $user_social->toArray();


        /** @var User $user_fetch */
        $user_fetch = $this->em->getRepository(User::class)->findOneBy(["email" => $data['email']]);
        $user_found = true;
        if (is_null($user_fetch)) {
            $user_found = false;
            // default value si utilisateur n'est pas encore membre
            // $plain_password = "offre";
            $user_fetch = (new User())
                ->setPassword(
                    $this->hasher->hashPassword(new User(), static::DEFAULT_PLAIN_PASSWORD)
                )
                ->setStatus(true)
                ->setUsername($data['email']);

            foreach ($data as $prop => $v) {
                $method = 'set' . ucfirst($prop);
                if (method_exists($user_fetch, $method)) {
                    call_user_func([$user_fetch, $method], $v);
                }
            }

            $image_oauth = null;
            switch ($client_oauth) {
                case 'facebook':
                    $image_oauth = $data['picture_url'];
                    break;
                case 'google':
                    $image_oauth = $data['picture'];
                default:
                    break;
            }

            $this->uploadFile($user_fetch, $image_oauth);
            // ->setPassword(
            //     $this->hasher->hashPassword($user, $plain_password)
            // )
            // ->setStatus(true)
            // ->setUsername($username);

            $this->em->persist($user_fetch);
            $this->em->flush();
        }

        $token = $this->jWTTokenManager->create($user_fetch);

        $url_redirect_front = $this->redirect_url_front . "/login?token=$token";
        if (!$user_found) {
            $url_redirect_front .= "&plain-password=" . static::DEFAULT_PLAIN_PASSWORD . "&username=" . $user_fetch->getEmail();
        }

        /** rediriger vers le front */
        return new RedirectResponse($url_redirect_front);
    }

    private function uploadFile(User $user, $filename_oauth): User
    {
        $tempFilePath = sys_get_temp_dir() . '/' . uniqid() . '.jpg';
        file_put_contents($tempFilePath, file_get_contents($filename_oauth));
        $upload = new UploadedFile($tempFilePath, basename($tempFilePath), mime_content_type($tempFilePath), null, true);
        $media = new MediaObject();
        $media->file = $upload;
        $user->image = $media;


        return $user;
    }
}
