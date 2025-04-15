<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OAuthController extends AbstractController
{
    #[Route('/connect/{oauth}', name: 'connect_oauth_start')]
    public function connectAction(string $oauth, ClientRegistry $clientRegistry): Response
    {
        $scope = [
            'profile',
            'email' // the scopes you want to access
        ];
        switch ($oauth) {
            case 'facebook':
                $scope = [];
                break;
        }
        // will redirect to Facebook!
        return $clientRegistry
            ->getClient($oauth) // key used in config/packages/knpu_oauth2_client.yaml
            ->redirect($scope, []);
    }

    /**
     * After going to Facebook, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml
     *
     */
    #[Route("/connect/oauth/check", name: "connect_oauth_check")]
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry)
    {
        $client = $request->query->get('client');
        // ** if you want to *authenticate* the user, then
        // leave this method blank and create a Guard authenticator
        // (read below)

        /** @var \KnpU\OAuth2ClientBundle\Client\Provider\FacebookClient $client */
        $client = $clientRegistry->getClient($client);

        try {
            // the exact class depends on which provider you're using
            /** @var \League\OAuth2\Client\Provider\FacebookUser $user */
            $user = $client->fetchUser();

            // do something with all this new power!
            // e.g. $name = $user->getFirstName();
            dd($user);
            // ...
        } catch (IdentityProviderException $e) {
            // something went wrong!
            // probably you should return the reason to the user
            var_dump($e->getMessage());
            die;
        }
    }
}
