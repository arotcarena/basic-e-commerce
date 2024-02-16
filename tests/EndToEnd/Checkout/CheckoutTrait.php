<?php
namespace App\Tests\EndToEnd\Checkout;

use stdClass;
use Stripe\Stripe;
use App\Entity\Purchase;
use Stripe\PaymentIntent;
use App\Config\TextConfig;
use App\Config\SecurityConfig;
use Doctrine\ORM\EntityManagerInterface;

trait CheckoutTrait 
{
    private function createAndPersistEmptyPurchase(): Purchase
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $purchase = new Purchase;
        $em->persist($purchase);
        $em->flush();
        return $purchase;
    }

    private function createPaymentIntent(int $amount, int $purchaseId): PaymentIntent
    {
        Stripe::setApiKey(SecurityConfig::STRIPE_SECRET_KEY);
        return PaymentIntent::create([
            'amount' => $amount,  
            'currency' => 'eur',
            'metadata' => [
                'purchaseId' => $purchaseId 
            ],
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ]);
    }

    private function createValidCheckoutData(): stdClass
    {
        return (object)[
            'civilState' => (object)[
                'civility' => TextConfig::CIVILITY_M,
                'firstName' => 'civility_firstName',
                'lastName' => 'civility_lastName',
            ],
            'deliveryAddress' => (object)[
                'civility' => TextConfig::CIVILITY_M,
                'firstName' => 'delivery_firstName',
                'lastName' => 'delivery_lastName',
                'lineOne' => 'delivery_lineOne',
                'lineTwo' => 'delivery_lineTwo',
                'postcode' => '75000',
                'city' => 'delivery_city',
                'country' => 'delivery_country',
            ],
            'invoiceAddress' => (object)[
                'lineOne' => 'invoice_lineOne',
                'lineTwo' => 'invoice_lineTwo',
                'postcode' => '75000',
                'city' => 'invoice_city',
                'country' => 'invoice_country',
            ],
        ];
    }
}