<?php

namespace App\Tests\src\Controller\Payum;

use App\Entity\Payment;
use App\Entity\Token;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\PaymentService;
use App\Traits\FixturesTrait;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class PaymentControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;

    protected ?PaymentService $paymentService;
    protected ?KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->paymentService = $this->getContainer()->get(PaymentService::class);
    }

    public function testPaymentStart(): void
    {
        $this->client->request('GET', '/payum/start');

        $this->assertResponseRedirects();
    }

    public function testPayumDone(): void
    {
        $this->loadFixturesTrait();

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        /** @var User $user */
        $user = $this->all_fixtures['user_1'];

        $container = static::getContainer();
        /** @var EntityManagerInterface  */
        $em = $container->get(EntityManagerInterface::class);

        // Créer un paiement de test
        $payment = new Payment();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(123);
        $payment->setDescription('Test payment');
        $payment->setClientId($user->getId());
        $payment->setClientEmail($user->getEmail());
        $payment->setDetails([
            'paid' => true,
            'billing_details' => [
                'name' => $user->getEmail()
            ]
        ]);

        $em->persist($payment);

        // Créer un Token de test
        $token = new Token();
        $token->setHash(uniqid());
        $token->setTargetUrl('/payum/done');
        $token->setGatewayName('default');
        $token->setDetails($payment);

        // Sauvegarder le token (si vous utilisez le stockage de tokens)
        $em->persist($token);
        $em->flush();

        // Mock Payum pour retourner notre paiement de test
        $payum = $this->createMock(Payum::class);
        $httpRequestVerifier = $this->createMock(HttpRequestVerifierInterface::class);

        $gateway = $this->createMock(GatewayInterface::class);
        $gateway->method('execute')->willReturnCallback(function ($status) use ($payment) {
            $status->markCaptured();
            $status->setFirstModel($payment);
        });

        // Configurer le mock pour vérifier le token
        $httpRequestVerifier->method('verify')
            ->with($this->callback(function ($request) {
                return $request->query->has('payum_token');
            }))
            ->willReturn($token);

        $payum->method('getHttpRequestVerifier')->willReturn($httpRequestVerifier);
        $payum->method('getGateway')->willReturn($gateway);

        // Faire la requête AVEC le token
        $this->client->request('GET', '/payum/done', [
            'payum_token' => $token->getHash()
        ]);
        // Configurer l'URL de redirection frontale
        $redirectUrl = $container->getParameter('redirect_url_front');

        // Vérifier la réponse
        $this->assertResponseRedirects();

        $response = $this->client->getResponse();
        $redirectLocation = $response->headers->get('Location');

        // Vérifier que l'URL de redirection contient un token JWT
        $this->assertStringStartsWith($redirectUrl . '?token=', $redirectLocation);

        // Rafraîchir l'utilisateur depuis la base de données
        /** @var User */
        $updatedUser = $userRepository->findOneBy(['email' => $user->getEmail()]);

        // Vérifier les changements
        $this->assertContains('ROLE_ADMIN', $updatedUser->getRoles());
        $this->assertTrue($updatedUser->getSociety()->isStatus());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
        $this->paymentService = null;
    }
}
