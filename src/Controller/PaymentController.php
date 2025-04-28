<?php

namespace App\Controller;

use App\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PaymentController extends AbstractController
{
    #[Route('/payment', name: 'payum_payment_done')]
    public function index(Request $request, PaymentService $payment): Response
    {
        // $token = $payum->getHttpRequestVerifier()->verify($request);

        // $gateway = $payum->getGateway($token->getGatewayName());

        // // You can invalidate the token, so that the URL cannot be requested any more:
        // // $payum->getHttpRequestVerifier()->invalidate($token);

        // // Once you have the token, you can get the payment entity from the storage directly. 
        // // $identity = $token->getDetails();
        // // $payment = $payum->getStorage($identity->getClass())->find($identity);

        // // Or Payum can fetch the entity for you while executing a request (preferred).
        // $gateway->execute($status = new GetHumanStatus($token));
        // $payment = $status->getFirstModel();

        // // Now you have order and payment status

        // return new JsonResponse(array(
        //     'status' => $status->getValue(),
        //     'payment' => array(
        //         'total_amount' => $payment->getTotalAmount(),
        //         'currency_code' => $payment->getCurrencyCode(),
        //         'details' => $payment->getDetails(),
        //     ),
        // ));
        return $payment->payementDone($request);
    }
}
