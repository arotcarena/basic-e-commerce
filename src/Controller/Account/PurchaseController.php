<?php
namespace App\Controller\Account;

use Exception;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Config\SiteConfig;
use App\Service\CartService;
use App\Config\SecurityConfig;
use App\Helper\FrDateTimeGenerator;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Email\Admin\AdminNotificationEmail;
use App\Email\Admin\AdminPurchaseConfirmationEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Email\Customer\PurchaseConfirmationEmail;
use App\Service\ProductCountService;
use App\Service\StockService;
use App\Service\StripeService;
use App\Twig\Runtime\PriceFormaterExtensionRuntime;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

#[IsGranted('ROLE_USER')]
class PurchaseController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private PurchaseRepository $purchaseRepository,
        private FrDateTimeGenerator $frDateTimeGenerator,
        private EntityManagerInterface $em,
        private PurchaseConfirmationEmail $purchaseConfirmationEmail,
        private AdminNotificationEmail $adminNotificationEmail,
        private AdminPurchaseConfirmationEmail $adminPurchaseConfirmationEmail,
        private PriceFormaterExtensionRuntime $priceFormater,
        private StockService $stockService,
        private StripeService $stripeService,
        private ProductCountService $productCountService
    )
    {

    }

    #[Route('/passer-commande', name: 'purchase_create')]
    public function create(): Response
    {
        if($this->cartService->count() === 0)
        {
            throw new NotFoundResourceException('Votre panier est vide, vous ne pouvez pas accéder à la page de validation de commande');
        }
        return $this->render('customer/purchase/create.html.twig');
    }

    #[Route('/commande-validee', name: 'purchase_paymentSuccess')]
    public function paymentSuccess(Request $request): Response 
    {
        //on récupére le paymentIntent à partir des infos dans l'url
        $paymentIntent = $this->stripeService->retrievePaymentIntent(
            $request->query->get('payment_intent_client_secret', ''), 
            $request->query->get('payment_intent', '')
        );

        //on récupère la purchase
        $purchase = $this->purchaseRepository->find($paymentIntent->metadata->purchaseId);
        if(!$purchase)
        {
            throw new NotFoundResourceException('Il semble y avoir eu un problème avec votre commande. Veuillez contacter le service client');
        }

        //on vérifie que la commande n'est pas déjà payée
        if($purchase->getStatus() !== SiteConfig::STATUS_PENDING)
        {
            $this->adminNotificationEmail->send('Une commande a probablement été payée 2 fois. Réf: '.$purchase->getRef().', email: '.$purchase->getUser()->getEmail());
            throw new Exception('Il semble y avoir eu un problème avec votre commande. Veuillez contacter le service client');
        }

        //on vérifie que le montant payé est bien le prix total de la purchase
        if($paymentIntent->amount_received !== $purchase->getTotalPrice())
        {
            $this->adminNotificationEmail->send(
                'Un problème a eu lieu sur une commande. Le montant réglé est différent du montant total de la commande. Réf. '.$purchase->getRef().'. Adresse email : '.$purchase->getUser()->getEmail()
            );
            throw new Exception('Un problème inattendu a eu lieu. Contactez le service client.');
        }

        //vérifie les stocks
        //vérifie que les prix des produits sont toujours identiques aux prix dans la purchase
        //si tout est bon update les stocks du shop
        if(!$this->stockService->verifyPurchaseStocksAndPriceAndUpdateStocksIfOk($purchase))
        {
            $this->adminNotificationEmail->send(
                'Un problème a eu lieu sur une commande. Le stock a du être modifié juste au moment du paiement, le client a payé une commande mais les stocks sont insuffisant. Réf. '.$purchase->getRef().'. Adresse email : '.$purchase->getUser()->getEmail()
            );
            throw new Exception('Un problème inattendu a eu lieu. Contactez le service client.');
        }

        //on marque la commande comme payée
        $purchase->setStatus(SiteConfig::STATUS_PAID);
        $purchase->setPaidAt($this->frDateTimeGenerator->generateImmutable());
        $this->em->flush();

        //on ajoute les sales aux products concernés
        $this->productCountService->countSales($purchase);

        //on vide le panier
        $this->cartService->empty();

        //envoi du mail de confirmation de commande
        $this->purchaseConfirmationEmail->send($purchase);
        $this->adminPurchaseConfirmationEmail->send($purchase);


        $this->addFlash('success', 'Merci de votre commande. Nous avons bien reçu votre paiement d\'un montant de '.$this->priceFormater->format($paymentIntent->amount_received).'. Un email récapitulatif vous a été envoyé. Retrouvez vos commande dans votre espace client, dans Mon compte > mes commandes');
        return $this->redirectToRoute('home');
    }
}