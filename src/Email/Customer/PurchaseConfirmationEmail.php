<?php
namespace App\Email\Customer;

use App\Config\SiteConfig;
use App\Email\EmailFactory;
use App\Entity\Purchase;
use Symfony\Component\Mime\Email;

class PurchaseConfirmationEmail extends EmailFactory
{
    public function send(Purchase $purchase)
    {
        $email = (new Email())
            ->from(SiteConfig::EMAIL_NOREPLY)
            ->to($purchase->getUser()->getEmail())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Confirmation de commande')
            ->text('Merci pour votre commande')
            ->html($this->twig->render('email/customer/purchase_confirmation.html.twig', [
                'purchase' => $purchase
            ]));

        $this->sendEmail($email);
    }
}