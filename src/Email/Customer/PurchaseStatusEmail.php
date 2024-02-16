<?php
namespace App\Email\Customer;

use App\Config\SiteConfig;
use App\Email\EmailFactory;
use App\Entity\Purchase;
use Symfony\Component\Mime\Email;

class PurchaseStatusEmail extends EmailFactory
{
    public function send(Purchase $purchase, string $newStatus): void
    {
        switch($newStatus) 
        {
            case SiteConfig::STATUS_PAID:
                $this->sendEmail($this->createPaidStatusEmail($purchase));
                break;
            case SiteConfig::STATUS_SENT:
                $this->sendEmail($this->createSentStatusEmail($purchase));
                break;
            case SiteConfig::STATUS_DELIVERED:
                $this->sendEmail($this->createDeliveredStatusEmail($purchase));
                break;
            case SiteConfig::STATUS_CANCELED:
                $this->sendEmail($this->createCanceledStatusEmail($purchase));
                break;
            default:
                return;
            
        }
    }

    public function createPaidStatusEmail(Purchase $purchase): Email 
    {
        return (new Email())
            ->from(SiteConfig::EMAIL_NOREPLY)
            ->to($purchase->getUser()->getEmail())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Votre commande est validée !')
            ->text('Nous avons bien reçu votre réglement pour la commande n°'.$purchase->getRef())
            ->html($this->twig->render('email/customer/purchaseStatus/paid_status.html.twig', [
                'purchase' => $purchase
            ]));
    }

    public function createSentStatusEmail(Purchase $purchase): Email 
    {
        return (new Email())
            ->from(SiteConfig::EMAIL_NOREPLY)
            ->to($purchase->getUser()->getEmail())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Votre commande a été expédiée !')
            ->text('Votre commande n°'.$purchase->getRef().' a été expédiée')
            ->html($this->twig->render('email/customer/purchaseStatus/sent_status.html.twig', [
                'purchase' => $purchase
            ]));
    }

    public function createDeliveredStatusEmail(Purchase $purchase): Email
    {
        return (new Email())
            ->from(SiteConfig::EMAIL_NOREPLY)
            ->to($purchase->getUser()->getEmail())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Votre commande a été livrée !')
            ->text('Votre commande n°'.$purchase->getRef().' a été livrée')
            ->html($this->twig->render('email/customer/purchaseStatus/delivered_status.html.twig', [
                'purchase' => $purchase
            ]));
    }

    public function createCanceledStatusEmail(Purchase $purchase): Email
    {
        return (new Email())
            ->from(SiteConfig::EMAIL_NOREPLY)
            ->to($purchase->getUser()->getEmail())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Votre commande a été annulée !')
            ->text('Votre commande n°'.$purchase->getRef().' a été annulée')
            ->html($this->twig->render('email/customer/purchaseStatus/canceled_status.html.twig', [
                'purchase' => $purchase
            ]));

    }
}