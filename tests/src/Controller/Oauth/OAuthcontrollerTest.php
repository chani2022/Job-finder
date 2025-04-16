<?php

namespace App\Tests\src\Controller\Oauth;

use App\Controller\OAuthController;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OAuthcontrollerTest extends TestCase
{
    public function testConnectAction(): void
    {
        $oauth = "google";
        $clientRegistry = $this->createMock(ClientRegistry::class);
        $redirectResponse = new RedirectResponse("/");

        $oauth2Client = $this->createMock(OAuth2ClientInterface::class);

        $clientRegistry->expects($this->once())
            ->method('getClient')
            ->with($oauth)
            ->willReturn($oauth2Client);

        $oauth2Client->expects($this->once())
            ->method('redirect')
            ->with(["profile", 'email'], [])
            ->willReturn($redirectResponse);

        $oauthController = new OAuthController();

        $this->assertEquals($redirectResponse, $oauthController->connectAction($oauth, $clientRegistry));
    }
}
