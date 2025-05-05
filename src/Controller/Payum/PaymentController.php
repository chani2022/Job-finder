<?php

namespace App\Controller\Payum;

use App\Entity\Society;
use App\Entity\User;
use App\Service\PaymentService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

final class PaymentController extends AbstractController
{
    private PaymentService $paymentService;
    private EntityManagerInterface $em;
    private string $redirect_url_front;

    public function __construct(PaymentService $paymentService, EntityManagerInterface $em, string $redirect_url_front)
    {
        $this->paymentService = $paymentService;
        $this->em = $em;
        $this->redirect_url_front = $redirect_url_front;
    }

    #[Route("/payum/start", name: "payum_start")]
    public function payumStart(): RedirectResponse
    {
        return $this->paymentService->prepare();
    }

    #[Route("/payum/done", name: "payum_payment_done")]
    public function payumDone(Request $request, JWTTokenManagerInterface $jWTTokenManager): RedirectResponse
    {
        /** @var JsonResponse */
        $response = $this->paymentService->paymentDone($request);

        $data = json_decode($response->getContent(), true);

        if ($data['payment']['details']['paid']) {
            $email = $data['payment']['details']['billing_details']['name'];
            /** @var User  */
            $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
            /** @var Society */
            $society = $user->getSociety();

            $user->setRoles(['ROLE_ADMIN']);
            $society->setStatus(true);

            $this->em->flush();

            $this->redirect_url_front .= '?token=' . $jWTTokenManager->create($user);
        }

        return new RedirectResponse($this->redirect_url_front);
    }
}
