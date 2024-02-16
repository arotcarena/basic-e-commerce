<?php
namespace App\Email\Admin;

use App\Config\SiteConfig;
use App\Email\EmailFactory;
use Symfony\Component\Mime\Email;

class AdminNotificationEmail extends EmailFactory
{
    public function send(string $message)
    {
        $email = (new Email())
            ->from(SiteConfig::EMAIL_NOREPLY)
            ->to(SiteConfig::EMAIL_ADMIN)
            ->subject('Notification importante')
            ->text($message)
            ->html('<p>'.$message.'</p>');

        $this->sendEmail($email);
    }
}