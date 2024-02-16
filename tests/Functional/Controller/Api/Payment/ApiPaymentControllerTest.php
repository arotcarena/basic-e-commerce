<?php
namespace App\Tests\Functional\Controller\Api\Payment;

use App\Config\SiteConfig;
use App\DataFixtures\Tests\CartTestFixtures;
use App\Tests\Functional\FunctionalTest;
use Symfony\Component\HttpFoundation\Response;
use App\DataFixtures\Tests\PurchaseTestFixtures;
use App\Repository\CartRepository;
use App\Repository\PurchaseRepository;
use App\Service\StripeService;

/**
 * @group FunctionalApi
 */
class ApiPaymentControllerTest extends FunctionalTest
{
    private StripeService $stripeService;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([PurchaseTestFixtures::class, CartTestFixtures::class]);

        $this->stripeService = $this->client->getContainer()->get(StripeService::class);
    }

    public function testCreatePaymentIntentWithAmountZeroAsParam()
    {
        $this->client->request('POST', $this->urlGenerator->generate('api_payment_createPaymentIntent'), [], [], [], json_encode(0));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    public function testCreatePaymentIntentReturnCorrectClientSecretAmountIfAmountIsPassed()
    {
        $this->client->request('POST', $this->urlGenerator->generate('api_payment_createPaymentIntent'), [], [], [], json_encode(300));
        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent());
        $paymentIntent = $this->stripeService->retrievePaymentIntent($data->clientSecret);

        $this->assertEquals(
            300,
            $paymentIntent->amount
        );
    }
    
    public function testCreatePaymentIntentCreateEmptyPurchaseAndReturnHisIdInPaymentIntentMetadata()
    {
        $this->client->request('POST', $this->urlGenerator->generate('api_payment_createPaymentIntent'), [], [], [], json_encode(200));
        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent());
        $paymentIntent = $this->stripeService->retrievePaymentIntent($data->clientSecret);

        $id = $paymentIntent->metadata->purchaseId;
        $purchase = $this->findEntity(PurchaseRepository::class, ['id' => $id]);
        $this->assertNotNull($purchase, 'L\'id retourné ne correspond à aucune purchase');
        $this->assertNull($purchase->getRef(), 'La purchase devrait être vide mais elle a une Ref');
    }

}