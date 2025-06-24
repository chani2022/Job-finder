<?php

namespace App\Mailer;

use App\Service\FileEmailAttachementLocator;
use App\Service\FilesystemLocatorTemplate;
use BadMethodCallException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Mailer\MailerInterface;

class ServiceMailer
{
    private MailerInterface $mailer;
    private TemplatedEmail $templatedEmail;

    public function __construct(
        MailerInterface $mailer,
        private readonly FileEmailAttachementLocator $fileAttachement,
        private readonly FilesystemLocatorTemplate $templateLocator,
        ?TemplatedEmail $templatedEmail = null
    ) {
        $this->mailer = $mailer;
        $this->templatedEmail = $templatedEmail ?? new TemplatedEmail();
    }

    public function to(string $to): self
    {
        $this->templatedEmail->to($to);

        return $this;
    }

    public function getTo(): array
    {
        return $this->templatedEmail->getTo();
    }

    public function from(string $from): self
    {
        $this->templatedEmail->from($from);

        return $this;
    }

    public function getFrom(): array
    {
        return $this->templatedEmail->getFrom();
    }

    public function subject(string $subject): self
    {
        $this->templatedEmail->subject($subject);

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->templatedEmail->getSubject();
    }

    public function htmlTemplate(string $template): self
    {
        $this->templatedEmail->htmlTemplate($template);

        return $this;
    }

    public function getHtmlTemplate(): ?string
    {
        return $this->templatedEmail->getHtmlTemplate();
    }

    public function attachFile(string $path, ?string $name = null): static
    {
        $this->templatedEmail->attachFromPath($path, $name);

        return $this;
    }

    public function getAttachFile(): array
    {
        return $this->templatedEmail->getAttachments();
    }

    public function context(array $context): static
    {
        if (is_null($this->getHtmlTemplate())) {
            throw new BadMethodCallException('Vous devez appeler la methode htmlTemplate avant');
        }

        $this->templatedEmail->context($context);

        return $this;
    }

    public function getContext(): array
    {
        return  $this->templatedEmail->getContext();
    }

    public function send(): void
    {
        $this->mailer->send($this->templatedEmail);
    }
}
