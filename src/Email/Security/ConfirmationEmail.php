<?php
namespace App\Email\Security;

use App\Config\SiteConfig;
use App\Email\EmailFactory;
use App\Entity\User;
use Symfony\Component\Mime\Email;

class ConfirmationEmail extends EmailFactory
{
    public function send(User $user)
    {
        $link = SiteConfig::SITE_URL .
                $this->urlGenerator->generate('security_emailConfirmation') .
                '?token='.$user->getId().'=='.$user->getConfirmationToken();

        $email = (new Email())
            ->from(SiteConfig::EMAIL_NOREPLY)
            ->to($user->getEmail())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Confirmez votre adresse email')
            ->text('Bienvenue sur '.SiteConfig::SITE_NAME.'! Veuillez suivre le lien suivant pour activer votre compte : '.$link)
            ->html($this->twig->render('email/security/confirmation_email.html.twig', [
                'link' => $link
            ]));

        $this->sendEmail($email);
    }
}