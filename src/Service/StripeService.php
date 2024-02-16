<?php
namespace App\Service;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Config\SecurityConfig;
use Exception;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(SecurityConfig::STRIPE_SECRET_KEY);
    }

    public function retrievePaymentIntent(string $piSecret, string $piPublic = null): ?PaymentIntent
    {
        $pi = $piPublic ?: explode('_secret_', $piSecret)[0];

        try
        {
            return PaymentIntent::retrieve($pi, [
                'client_secret' => $piSecret
            ]);
        }
        catch(Exception $e)
        {
            return null;
        }
    }

    public function createPaymentIntent(int $amount, array $metadata = []): ?PaymentIntent
    {
        try
        {
            return PaymentIntent::create([
                'amount' => $amount,  
                'currency' => 'eur',
                'metadata' => $metadata,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);
        }
        catch(Exception $e)
        {
            return null;
        }
    }
}