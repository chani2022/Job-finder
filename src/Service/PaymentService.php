<?php

namespace App\Service;

use App\Entity\Payment;
use Payum\Core\Payum;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\User;
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

    public function prepare()
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        $gatewayName = 'stripe_checkout';

        $storage = $this->payum->getStorage(Payment::class);

        /** @var Payment $payment */
        $payment = $storage->create();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(123); // 1.23 EUR
        $payment->setDescription('A description');
        $payment->setClientId($user->getId());
        $payment->setClientEmail($user->getEmail());


        $storage->update($payment);

        $captureToken = $this->payum->getTokenFactory()->createCaptureToken(
            $gatewayName,
            $payment,
            'payum_payment_done' // the route to redirect after capture
        );

        return new RedirectResponse($captureToken->getTargetUrl());
    }
}
