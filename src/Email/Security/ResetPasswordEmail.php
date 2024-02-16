<?php
namespace App\Email\Security;

use App\Config\SiteConfig;
use App\Email\EmailFactory;
use App\Entity\User;
use Symfony\Component\Mime\Email;

class ResetPasswordEmail extends EmailFactory
{
    public function send(User $user)
    {
        $link = SiteConfig::SITE_URL .
                $this->urlGenerator->generate('security_resetPassword') .
                '?token='.$user->getId().'=='.$user->getResetPasswordToken();

        $email = (new Email())
            ->from(SiteConfig::EMAIL_NOREPLY)
            ->to($user->getEmail())
            ->subject('Réinitialisez votre mot de passe')
            ->text('Vous avez demandé la réinitialisation de votre mot de passe. Veuillez suivre le lien suivant pour créer un nouveau mot de passe : '.$link)
            ->html($this->twig->render('email/security/reset_password_email.html.twig', [
                'link' => $link
            ]));

        $this->sendEmail($email);
    }
}