<?php

namespace App\Tests\src\Service;

use App\Entity\Payment;
use App\Service\PaymentService;
use App\Traits\FixturesTrait;
use App\Traits\LogUserTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\TokenInterface as PayumTokenInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PaymentServiceTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;
    use LogUserTrait;

    private ?PaymentService $paymentService;

    /** @var MockObject|Payum|null $payum */
    private $payum;
    /** @var User|null $user */
    private $user_1;


    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->loadFixturesTrait();

        $this->user_1 = $this->all_fixtures['user_1'];

        $this->payum = $this->createMock(Payum::class);

        $securityToken = $this->createMock(TokenInterface::class);
        $securityToken->method('getUser')->willReturn($this->user_1);

        $securityTokenStorage = $this->createMock(TokenStorageInterface::class);
        $securityTokenStorage->method('getToken')->willReturn($securityToken);

        $this->paymentService = new PaymentService($this->payum, $securityTokenStorage);
    }

    public function testPrepare(): void
    {

        $storage = $this->createMock(StorageInterface::class);
        $this->payum->expects($this->once())
            ->method('getStorage')
            ->with(Payment::class)
            ->willReturn($storage);

        $payment = new Payment();
        $url = "payum_payment_done";
        $gatewayNameExcepted = 'default';


        $storage->expects($this->once())
            ->method('create')
            ->willReturn($payment);

        $storage->expects($this->once())
            ->method('update')
            ->with($payment);

        $tokenFactory = $this->createMock(GenericTokenFactory::class);
        $captureToken = $this->createMock(PayumTokenInterface::class);

        $this->payum->expects($this->once())
            ->method('getTokenFactory')
            ->willReturn($tokenFactory);

        $tokenFactory->expects($this->once())
            ->method('createCaptureToken')
            ->with($this->callback(function ($gatewayName) use ($gatewayNameExcepted) {
                return $gatewayName == $gatewayNameExcepted;
            }), $this->callback(function ($payment_obj) use ($payment) {
                return $payment_obj == $payment;
            }), $this->callback(function ($url_redirect) use ($url) {
                return $url_redirect == $url;
            }))
            ->willReturn($captureToken);

        $captureToken->expects($this->once())
            ->method('getTargetUrl')
            ->willReturn($url);

        $redirect = new RedirectResponse($url);

        $response = $this->paymentService->prepare();

        $this->assertInstanceOf(RedirectResponse::class, $response);

        $this->assertEquals($redirect->getTargetUrl(), $response->getTargetUrl());

        $this->assertEquals($this->user_1->getId(), $payment->getClientId());
        $this->assertEquals($this->user_1->getEmail(), $payment->getClientEmail());
        $this->assertEquals("EUR", $payment->getCurrencyCode());
        $this->assertEquals(123, $payment->getTotalAmount());
    }

    public function testPayementDone(): void
    {

        // Arrange
        $requestVerifier = $this->createMock(HttpRequestVerifierInterface::class);
        $token = $this->createMock(PayumTokenInterface::class);
        $gateway = $this->createMock(GatewayInterface::class);
        $paymentModel = new Payment(); // Remplace stdClass par ta classe Payment si tu en as une

        $request = new Request();

        // Simuler le HttpRequestVerifier
        $this->payum->method('getHttpRequestVerifier')
            ->willReturn($requestVerifier);

        $requestVerifier->method('verify')
            ->with($request)
            ->willReturn($token);

        // Simuler le token
        $token->method('getGatewayName')
            ->willReturn('gateway_name');

        // Simuler la récupération du gateway
        $this->payum->method('getGateway')
            ->with('gateway_name')
            ->willReturn($gateway);

        $paymentModel->setTotalAmount(5000);
        $paymentModel->setCurrencyCode('EUR');
        $paymentModel->setDetails(['foo' => 'bar']);

        // Simuler l'exécution du status
        $gateway->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function ($status) use ($paymentModel) {
                if ($status instanceof GetHumanStatus) {
                    $status->markCaptured();
                    $status->setModel($paymentModel);
                }
            });

        /** @var JsonResponse $jsonResponse */
        $jsonResponse = $this->paymentService->paymentDone($request);

        $data = json_decode($jsonResponse->getContent(), true);
        // Assert
        // $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals('captured', $data['status']);
        // $this->assertInstanceOf(GetHumanStatus::class, $status);
        $this->assertEquals(5000, $data['payment']['total_amount']);
        $this->assertEquals('EUR', $data['payment']['currency_code']);
        $this->assertEquals(['foo' => 'bar'], $data['payment']['details']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->paymentService = null;
        $this->user_1 = null;
        $this->payum = null;
    }
}
