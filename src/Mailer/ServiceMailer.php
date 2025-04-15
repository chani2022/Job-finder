<?php

namespace App\Mailer;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class ServiceMailer
{
    private MailerInterface $mailer;
    private RequestStack $requestStack;

    public function __construct(MailerInterface $mailer, RequestStack $requestStack)
    {
        $this->mailer = $mailer;
        $this->requestStack = $requestStack;
    }

    public function send(User $to, string $subject): void
    {
        $local = $this->requestStack->getCurrentRequest() ? $this->requestStack->getCurrentRequest()->getLocale() : 'fr';

        $email = (new TemplatedEmail())
            ->to(new Address($to->getEmail()))
            ->subject($subject)

            // path of the Twig template to render
            ->htmlTemplate('emails/confirmation_registration.html.twig')

            // change locale used in the template, e.g. to match user's locale
            ->locale($local)

            // pass variables (name => value) to the template
            ->context([
                'expiration_date' => new \DateTime('+7 days'),
                'user' => $to,
                'domaine_name' => $_ENV['DOMAINE_NAME']
            ]);

        $this->mailer->send($email);
    }
}
