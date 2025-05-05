<?php

namespace App\Tests\src\Controller\Oauth;

use App\Controller\OAuthController;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OAuthcontrollerTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private ?EntityManagerInterface $em;
    private ?JWTTokenManagerInterface $jWTTokenManager;
    private ?UserPasswordHasherInterface $hasher;
    private ?Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = static::getContainer();
        $this->em = $this->container->get(EntityManagerInterface::class);
        $this->jWTTokenManager = $this->container->get(JWTTokenManagerInterface::class);
        $this->hasher = $this->container->get(UserPasswordHasherInterface::class);
    }
    /**
     * @dataProvider provideClient
     */
    public function testConnectAction(string $oauth): void
    {
        $clientRegistry = $this->createMock(ClientRegistry::class);
        /** @var UrlGeneratorInterface */
        $urlGenerator = $this->container->get(UrlGeneratorInterface::class);
        $redirectResponse = new RedirectResponse($urlGenerator->generate("connect_oauth_check", ["client" => $oauth]));

        $oauth2Client = $this->createMock(OAuth2ClientInterface::class);

        $clientRegistry
            ->method('getClient')
            ->with($oauth)
            ->willReturn($oauth2Client);

        $oauth2Client->expects($this->once())
            ->method('redirect')
            ->with($this->callback(function ($scope) use ($oauth) {
                if ($oauth == "google") {
                    return $scope == ["profile", "email"];
                }
                return $scope == ['email', 'public_profile'];
            }), [])
            ->willReturn($redirectResponse);

        $oauthController = new OAuthController($this->em, $this->jWTTokenManager, $this->hasher, $this->container->getParameter('redirect_url_front'));
        $response = $oauthController->connectAction($oauth, $clientRegistry);

        $this->assertInstanceOf(RedirectResponse::class, $response);

        if ($oauth == 'google') {
            $this->assertStringContainsString("client=google", $response->getTargetUrl());
        } else {
            $this->assertStringContainsString("client=facebook", $response->getTargetUrl());
        }
    }
    /**
     * @dataProvider provideClient
     */
    public function testConnectCheckAction(string $oauth, array $data): void
    {
        $request = new Request();
        $request->query->set('client', $oauth);
        $clientRegistry = $this->createMock(ClientRegistry::class);
        $client = $this->createMock(OAuth2ClientInterface::class);

        $clientRegistry->expects($this->once())
            ->method('getClient')
            ->with($request->query->get('client'))
            ->willReturn($client);

        if ($oauth == "google") {
            $data['picture'] = $this->container->getParameter('path_source_image_test') . 'test.png';
        } else {
            $data['picture_url'] = $this->container->getParameter('path_source_image_test') . 'test.png';
        }

        $resourceOwner = $this->createMock(ResourceOwnerInterface::class);
        $resourceOwner->expects($this->once())
            ->method('toArray')
            ->willReturn($data);


        $client->expects($this->once())
            ->method('fetchUser')
            ->willReturn($resourceOwner);

        $oauthController = new OAuthController($this->em, $this->jWTTokenManager, $this->hasher, $this->container->getParameter('redirect_url_front'));

        $redirect_response = $oauthController->connectCheckAction($request, $clientRegistry);

        /** @var User $user_fetch */
        $user_fetch = $this->em->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        $this->assertNotNull($user_fetch);
        $this->assertTrue($user_fetch->isStatus());

        $this->assertInstanceOf(RedirectResponse::class, $redirect_response);

        if ($oauth == "facebook") {
            $this->assertStringContainsString("token=", $redirect_response->getTargetUrl());
            $this->assertStringContainsString("plain-password=", $redirect_response->getTargetUrl());
            $this->assertStringContainsString("username=", $redirect_response->getTargetUrl());
        } else {
            $this->assertStringContainsString("token=", $redirect_response->getTargetUrl());
            $this->assertStringNotContainsString("plain-password=", $redirect_response->getTargetUrl());
            $this->assertStringNotContainsString("username=", $redirect_response->getTargetUrl());
        }
    }

    public static function provideClient(): array
    {
        return [
            "user_in_db" => [
                "google",
                [
                    "email" => "admin@admin.com",
                    'picture' => "test.jpg"
                ]

            ],
            "user_not_in_db" => [
                "facebook",
                [
                    "email" => "email@email.com",
                    "picture_url" => "test.jpg"
                ]

            ]
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em = null;
        $this->jWTTokenManager = null;
        $this->hasher = null;
        $this->container = null;
    }
}
