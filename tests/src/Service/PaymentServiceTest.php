<?php

namespace App\Tests\src\Service;

use App\Entity\Payment;
use App\Service\PaymentService;
use App\Traits\FixturesTrait;
use App\Traits\LogUserTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Payum\Core\Payum;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Core\Security\TokenInterface as PayumTokenInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    public function testPreparePayment(): void
    {

        $storage = $this->createMock(StorageInterface::class);
        $this->payum->expects($this->once())
            ->method('getStorage')
            ->with(Payment::class)
            ->willReturn($storage);

        $payment = new Payment();
        $url = "payum_payment_done";
        $gatewayNameExcepted = 'stripe_checkout';


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

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->paymentService = null;
        $this->user_1 = null;
        $this->payum = null;
    }
}
