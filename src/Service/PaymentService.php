<?php

namespace App\Service;

use App\Entity\Payment;
use Payum\Core\Payum;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Payum\Core\Request\GetHumanStatus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PaymentService
{
    private Payum $payum;
    private TokenStorageInterface $tokenStorage;

    public function __construct(Payum $payum, TokenStorageInterface $tokenStorage)
    {
        $this->payum = $payum;
        $this->tokenStorage = $tokenStorage;
    }

    public function prepare(): RedirectResponse
    {
        /** @var User|null $user */
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        $gatewayName = 'default';

        $storage = $this->payum->getStorage(Payment::class);

        $price = 123;

        /** @var Payment $payment */
        $payment = $storage->create();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount($price); // 1.23 EUR
        $payment->setDescription('A description');
        $payment->setClientId($user ? $user->getId() : 1);
        $payment->setClientEmail($user ? $user->getEmail() : "test@test.com");

        $storage->update($payment);
        // $this->em->persist($payment);
        // $this->em->flush();

        $captureToken = $this->payum->getTokenFactory()->createCaptureToken(
            $gatewayName,
            $payment,
            'payum_payment_done' // the route to redirect after capture
        );

        return new RedirectResponse($captureToken->getTargetUrl());
    }

    public function paymentDone(Request $request): JsonResponse
    {

        $token = $this->payum->getHttpRequestVerifier()->verify($request);

        $gateway = $this->payum->getGateway($token->getGatewayName());

        // You can invalidate the token, so that the URL cannot be requested any more:
        // $payum->getHttpRequestVerifier()->invalidate($token);

        // Once you have the token, you can get the payment entity from the storage directly. 
        // $identity = $token->getDetails();
        // $payment = $payum->getStorage($identity->getClass())->find($identity);

        // Or Payum can fetch the entity for you while executing a request (preferred).
        $gateway->execute($status = new GetHumanStatus($token));
        $payment = $status->getFirstModel();
        // Now you have order and payment status

        return new JsonResponse(array(
            'status' => $status->getValue(),
            'payment' => array(
                'total_amount' => $payment->getTotalAmount(),
                'currency_code' => $payment->getCurrencyCode(),
                'details' => $payment->getDetails(),
            )
        ));
    }
}
