<?php
namespace App\Email;

use App\Config\SiteConfig;
use Exception;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

abstract class EmailFactory
{
    protected Mailer $mailer;

    protected Environment $twig;

    protected UrlGeneratorInterface $urlGenerator;

    public function __construct(Environment $twig, UrlGeneratorInterface $urlGenerator)
    {
        $transport = Transport::fromDsn(SiteConfig::SMTP);
        $this->mailer = new Mailer($transport);
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
    }

    public function sendEmail(Email $email)
    {
        // $this->mailer->send($email);
    }
}