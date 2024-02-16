<?php
namespace App\Controller\Api\Payment;

use Exception;
use App\Config\SiteConfig;
use App\Entity\Purchase;
use App\Helper\FrDateTimeGenerator;
use App\Repository\PurchaseRepository;
use App\Service\CartService;
use App\Service\StripeService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Translation\Exception\NotFoundResourceException;


class ApiPaymentController extends AbstractController
{
    public function __construct(
        private PurchaseRepository $purchaseRepository,
        private StripeService $stripeService,
        private CartService $cartService,
        private EntityManagerInterface $em,
        private FrDateTimeGenerator $frDateTimeGenerator
    )
    {
        
    }

    #[Route('/api/payment/createPaymentIntent', name: 'api_payment_createPaymentIntent')]
    public function createPaymentIntent(Request $request): JsonResponse 
    {
        //le totalPrice est envoyé depuis le javascript mais seulement si le cart avait eu le temps de charger.
        //sinon il faut le récupérer avec cartService
        $totalPrice = json_decode($request->getContent());
        if(!$totalPrice)
        {
            $lightCart = $this->cartService->getLightCart();
            $totalPrice = $lightCart['totalPrice'];
        }
        
        //on vérifie si égal à 0
        if($totalPrice === 0)
        {
            return $this->json([
                'errors' => ['Votre panier est vide']
            ], 500);
        }

        //on crée une purchase vide pour pouvoir passer l'id au paymentIntent
        $purchase = new Purchase;
        $this->em->persist($purchase);
        $this->em->flush();

        //on crée le paymentIntent et on renvoie le clientSecret
        $paymentIntent = $this->stripeService->createPaymentIntent(
            $totalPrice, 
            ['purchaseId' => $purchase->getId()]     // ce purchaseId sera ensuite passé par stripe à mon webhook qui écoute l'event payment_intent_succeeded
        );
        if(!$paymentIntent)
        {
            return $this->json([
                'errors' => ['Un problème est survenu. Veuillez réactualiser la page']
            ], 500);
        }
        return $this->json([
            'clientSecret' => $paymentIntent->client_secret,
        ]);
    }

    // /**
    //  * WEBHOOK qui écoute l'évenement au moment du payment succeeded  (tout ce qu'il fait est déjà fait dans PurchaseController.paymentSuccess, mais c'est utile au cas où le user ferme la page avant que purchaseController soit appelé)
    //  *
    //  * @param Request $request
    //  * @return Response
    //  */
    // #[Route('/api/webhook/payment-intent-succeeded-listener', name: 'api_webhook_paymentIntentSucceededListener')]
    // public function paymentIntentSucceededListener(Request $request): Response
    // {
    //     Stripe::setApiKey(SecurityConfig::STRIPE_SECRET_KEY);
    //     // Replace this endpoint secret with your endpoint's unique secret
    //     // If you are testing with the CLI, find the secret by running 'stripe listen'
    //     // If you are using an endpoint defined with the API or dashboard, look in your webhook settings
    //     // at https://dashboard.stripe.com/webhooks
    //     $endpoint_secret = 'whsec_f46d97c9097fcbdea9043fd136bef8c04eff677607e8d8e21ca7080e6ef1dd2f';

    //     $payload = $request->getContent();
    //     $event = null;

    //     try {
    //     $event = Event::constructFrom(
    //         json_decode($payload, true)
    //     );
    //     } catch(\UnexpectedValueException $e) {
    //     // Invalid payload
    //     echo '⚠️  Webhook error while parsing basic request.';
    //     http_response_code(400);
    //     exit();
    //     }
    //     if ($endpoint_secret) {
    //     // Only verify the event if there is an endpoint secret defined
    //     // Otherwise use the basic decoded event
    //     $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
    //     try {
    //         $event = \Stripe\Webhook::constructEvent(
    //         $payload, $sig_header, $endpoint_secret
    //         );
    //     } catch(\Stripe\Exception\SignatureVerificationException $e) {
    //         // Invalid signature
    //         echo '⚠️  Webhook error while validating signature.';
    //         http_response_code(400);
    //         exit();
    //     }
    //     }

    //     // Handle the event
    //     switch ($event->type) {
    //     case 'payment_intent.succeeded':
    //         $paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent

    //         //AGIR ICI : passer purchase au status paid, vider le panier
    //         //pour dumper event dans la console ajouter --print-json 
    //         //stripe listen --forward to localhost:4242/public/api/webhook/payment-intent-succeeded-listener --print-json
    //         $amount = $paymentIntent->amount;
    //         $amountRefunded = $paymentIntent->amount_refunded;
    //         $purchaseId = $event->metadata->purchaseId;
    //         $pi_key = $paymentIntent->payment_intent;
    //         $idPaid = $paymentIntent->paid;
    //         $isRefunded = $paymentIntent->refunded;
    //         // Then define and call a method to handle the successful payment intent.
    //         // handlePaymentIntentSucceeded($paymentIntent);
    //         break;
    //     case 'payment_method.attached':
    //         $paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod
    //         // Then define and call a method to handle the successful attachment of a PaymentMethod.
    //         // handlePaymentMethodAttached($paymentMethod);
    //         break;
    //     default:
    //         // Unexpected event type
    //         error_log('Received unknown event type');
    //     }

    //     return new Response();
    // }
}