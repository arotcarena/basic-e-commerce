<?php
namespace App\Tests\Functional\Controller\Api\Account;

use stdClass;
use Stripe\Stripe;
use App\Entity\Cart;
use App\Entity\User;
use App\Entity\Purchase;
use Stripe\PaymentIntent;
use App\Config\SiteConfig;
use App\Config\TextConfig;
use App\Config\SecurityConfig;
use App\Repository\CartRepository;
use App\Repository\UserRepository;
use App\Repository\PurchaseRepository;
use App\Service\ShippingCostCalculator;
use App\Tests\Functional\FunctionalTest;
use App\Tests\Functional\LoginUserTrait;
use Doctrine\ORM\EntityManagerInterface;
use App\DataFixtures\Tests\CartTestFixtures;
use Symfony\Component\HttpFoundation\Response;
use App\DataFixtures\Tests\PurchaseTestFixtures;
use Doctrine\Bundle\DoctrineBundle\DataCollector\DoctrineDataCollector;

/**
 * @group FunctionalApi
 */
class ApiPurchaseControllerTest extends FunctionalTest
{
    use LoginUserTrait;

    private ShippingCostCalculator $shippingCostCalculator;

    private EntityManagerInterface $em;

    private ?Cart $cart;

    private User $user;

    public function setUp(): void 
    {
        parent::setUp();

        $this->loadFixtures([CartTestFixtures::class, PurchaseTestFixtures::class]); // depends on UserTestFixtures & ProductTestFixtures

        /** @var User */
        $user = $this->findEntity(UserRepository::class, ['email' => 'confirmed_user@gmail.com']);  // user avec cart avec 2 cartLines
        $this->loginUser($user);
        $this->user = $user;

        $this->cart = $user->getCart();

        $this->shippingCostCalculator = $this->client->getContainer()->get(ShippingCostCalculator::class);
    
        $this->em = $this->client->getContainer()->get(EntityManagerInterface::class);
    }

    //auth
    // on ne teste pas auth ici car ça n'a pas vraiment d'importance


    //lastVerificationBeforePayment
    // en functional le cart est toujours vide car la session ne fonctionne pas
    public function testLastVerificationBeforePaymentWithEmptyCart()  // ex: si l'admin vient de supprimer le seul produit dans notre cart
    {
        $purchase = $this->createAndPersistEmptyPurchase();
        
        $paymentIntent = $this->createPaymentIntent(100, $purchase->getId());
        $this->client->request('POST', $this->urlGenerator->generate('api_purchase_lastVerificationBeforePayment', [
            'id' => $purchase->getId()
        ]), [], [], [], json_encode(['piSecret' => $paymentIntent->client_secret, 'checkoutData' => $this->createValidCheckoutData()]));

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $data = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(
            $this->urlGenerator->generate('home'),
            $data->errors->target
        );
    }
    
   
    public function testLastVerificationBeforePaymentDatabaseQueriesCount()
    {
        $purchase = $this->createAndPersistEmptyPurchase();

        $this->client->enableProfiler();
        
        $paymentIntent = $this->createPaymentIntent(100, $purchase->getId());
        $this->client->request('POST', $this->urlGenerator->generate('api_purchase_lastVerificationBeforePayment', [
            'id' => $purchase->getId()
        ]), [], [], [], json_encode(['piSecret' => $paymentIntent->client_secret, 'checkoutData' => $this->createValidCheckoutData()]));

        /** @var DoctrineDataCollector */
        $dbCollector = $this->client->getProfile()->getCollector('db');
        $this->assertLessThan(16, $dbCollector->getQueryCount(), 'dans mon premier test à 14');
    }
    

    private function createAndPersistEmptyPurchase(): Purchase
    {
        $purchase = new Purchase;
        $this->em->persist($purchase);
        $this->em->flush();
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