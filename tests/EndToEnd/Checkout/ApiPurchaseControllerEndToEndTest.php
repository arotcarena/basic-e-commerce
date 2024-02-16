<?php
// namespace App\Tests\EndToEnd\Checkout;

// use App\Repository\PurchaseRepository;
// use App\Service\StripeService;
// use App\Tests\EndToEnd\EndToEndTest;
// use App\Tests\EndToEnd\Utils\CartTrait;
// use App\Twig\Runtime\PriceFormaterExtensionRuntime;

// class ApiPurchaseControllerEndToEndTest extends EndToEndTest
// {
//     use CartTrait;

//     use CheckoutTrait;

//     private StripeService $stripeService;

//     private PriceFormaterExtensionRuntime $priceFormater;

//     public function setUp(): void 
//     {
//         parent::setUp();

//         $this->stripeService = static::getContainer()->get(StripeService::class);

//         $this->priceFormater = static::getContainer()->get(PriceFormaterExtensionRuntime::class);

//     }


//     // endToEnd obligé car en Functional le panier (session) ne fonctionne pas


//     public function testLastVerificationBeforePaymentWithEmptyCart()  // ex: si l'admin vient de supprimer le seul produit dans notre cart
//     {
//         $purchase = $this->createAndPersistEmptyPurchase();
        
//         $paymentIntent = $this->createPaymentIntent(100, $purchase->getId());
//         $this->client->request('GET', $this->urlGenerator->generate('api_purchase_lastVerificationBeforePayment', [
//             'id' => $purchase->getId()
//         ]), [], [], [], json_encode(['piSecret' => $paymentIntent->client_secret, 'checkoutData' => $this->createValidCheckoutData()]));

//         $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
//         $data = json_decode($this->client->getResponse()->getContent());
//         $this->assertEquals(
//             $this->urlGenerator->generate('home'),
//             $data->errors->target
//         );
//     }

//     public function testLastVerificationBeforePaymentWithInvalidPurchaseIdParam()
//     {
//          a faire en endtoend car la le panier est vide donc ça échoue bien mais pour la mauvaise raison
         
//         $paymentIntent = $this->createPaymentIntent(200, 123456789456123);
//         $this->client->request('POST', $this->urlGenerator->generate('api_purchase_lastVerificationBeforePayment'), 
//                                 [], [], [], json_encode(['piSecret' => $paymentIntent->client_secret, 'checkoutData' => $this->createValidCheckoutData()]));
//         $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
//     }

//     public function testLastVerificationBeforePaymentWithAlreadyPaidPurchaseId()
//     {
//         $purchase = $this->findEntity(PurchaseRepository::class, ['status' => SiteConfig::STATUS_PAID]);
//         $paymentIntent = $this->createPaymentIntent($purchase->getTotalPrice(), $purchase->getId());
//         $this->client->request('POST', $this->urlGenerator->generate('api_purchase_lastVerificationBeforePayment', [
//             'id' => $purchase->getId()
//         ]), [], [], [], json_encode($paymentIntent->client_secret));

//         $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
//         $data = json_decode($this->client->getResponse()->getContent());
//         $this->assertNotNull($data->errors);
//     }
//     public function testLastVerificationBeforePaymentWithPaymentIntentNotEqualToTotalPrice()  // ex: si l'admin vient de supprimer l'un des produits dans notre purchase
//     {
//         /** @var Purchase */
//         $purchase = $this->findEntity(PurchaseRepository::class, ['ref' => 'valid_purchase']); 
        
//         //on crée le paymentIntent comme ce serait fait en vrai mais en modifiant le amount
//         $paymentIntent = $this->createPaymentIntent(($purchase->getTotalPrice() + 100), $purchase->getId());

//         $this->client->request('POST', $this->urlGenerator->generate('api_purchase_lastVerificationBeforePayment', [
//             'id' => $purchase->getId()
//         ]), [], [], [], json_encode($paymentIntent->client_secret));

//         $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
//         $data = json_decode($this->client->getResponse()->getContent());
//         $this->assertEquals(
//             $this->urlGenerator->generate('purchase_create'),
//             $data->errors->target
//         );
//     }

//     public function testLastVerificationBeforePaymentWithOverStockPurchaseAndRemainingProduct()
//     {
//         $purchase = $this->findEntity(PurchaseRepository::class, ['ref' => 'purchase_test_one_product_over_stock']);  //purchase avec deux produits dont un over stock
//         $paymentIntent = $this->createPaymentIntent($purchase->getTotalPrice(), $purchase->getId());

//         $this->client->request('POST', $this->urlGenerator->generate('api_purchase_lastVerificationBeforePayment', [
//             'id' => $purchase->getId()
//         ]), [], [], [], json_encode($paymentIntent->client_secret));

//         $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
//         $data = json_decode($this->client->getResponse()->getContent());
//         // comme il reste un produit dans le panier, on doit être redirigé vers checkout
//         $this->assertEquals(
//             $this->urlGenerator->generate('purchase_create'),
//             $data->errors->target
//         );
//     }
//     public function testLastVerificationBeforePaymentWithOverStockPurchaseAndNoRemainingProduct()
//     {
//         $purchase = $this->findEntity(PurchaseRepository::class, ['ref' => 'purchase_test_all_over_stock']); // purchase avec un produit over stock avec stock à 0
//         $paymentIntent = $this->createPaymentIntent($purchase->getTotalPrice(), $purchase->getId());

//         $this->client->request('POST', $this->urlGenerator->generate('api_purchase_lastVerificationBeforePayment', [
//             'id' => $purchase->getId()
//         ]), [], [], [], json_encode($paymentIntent->client_secret));

//         $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
//         $data = json_decode($this->client->getResponse()->getContent());
//         // comme le panier se retrouve supprimé complétement, on doit être redirigé vers home
//         $this->assertEquals(
//             $this->urlGenerator->generate('home'),
//             $data->errors->target
//         );
//     }
//     public function testLastVerificationBeforePaymentWithOverStockGenerateCartUpdate()
//     {
//         $purchase = $this->findEntity(PurchaseRepository::class, ['ref' => 'purchase_for_cart_update_test']); //purchase avec un produit x 10 dont le stock = 1, et cart correspondant persisté
//         $user = $purchase->getUser();
//         $cart = $user->getCart();
//         $cartId = $cart->getId();
//         $this->assertEquals(10, $cart->getCount());
//         $this->loginUser($user);
        
//         $paymentIntent = $this->createPaymentIntent($purchase->getTotalPrice(), $purchase->getId());
//         $this->client->request('POST', $this->urlGenerator->generate('api_purchase_lastVerificationBeforePayment', [
//             'id' => $purchase->getId()
//         ]), [], [], [], json_encode($paymentIntent->client_secret));

//         $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
//         //on récupère le cart updated depuis repository;
//         $updatedCart = $this->findEntity(CartRepository::class, ['id' => $cartId]);
//         // on vérifie que le nombre d'articles dans le panier a changé, ça doit être 1 maintenant
//         $this->assertEquals(1, $updatedCart->getCount());
//     }

//     public function testLastVerificationBeforePaymentWithValidPurchase()
//     {
//         $purchase = $this->findEntity(PurchaseRepository::class, ['ref' => 'valid_purchase']);
//         $paymentIntent = $this->createPaymentIntent($purchase->getTotalPrice(), $purchase->getId());
//         $this->client->request('POST', $this->urlGenerator->generate('api_purchase_lastVerificationBeforePayment', [
//             'id' => $purchase->getId()
//         ]), [], [], [], json_encode($paymentIntent->client_secret));
//         $this->assertResponseIsSuccessful();
//     }


   
// }