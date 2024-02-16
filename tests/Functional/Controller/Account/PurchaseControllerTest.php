<?php
namespace App\Tests\Functional\Controller\Account;

use App\Config\SiteConfig;
use App\DataFixtures\Tests\ProductTestFixtures;
use App\DataFixtures\Tests\PurchaseTestFixtures;
use App\DataFixtures\Tests\UserTestFixtures;
use App\Repository\ProductRepository;
use App\Repository\PurchaseRepository;
use App\Service\StripeService;
use App\Tests\Functional\FunctionalTest;
use App\Tests\Functional\LoginUserTrait;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Stripe\Stripe;
use Stripe\StripeClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

/**
 * @group FunctionalAccount
 */
class PurchaseControllerTest extends FunctionalTest
{
    use LoginUserTrait;


    public function setUp(): void 
    {
        parent::setUp();

        $this->loadFixtures([UserTestFixtures::class, ProductTestFixtures::class, PurchaseTestFixtures::class]);

        
    }

    // auth
    public function testNotLoggedUserCannotAccess()
    {
        //create
        $this->client->request('GET', $this->urlGenerator->generate('purchase_create'));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));
    }

    //create
    public function testCreateNotAccessibleWithEmptyCart()
    {
        $this->loginUser();
        
        $this->client->request('GET', $this->urlGenerator->generate('purchase_create'));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    //paymentSuccess
    public function testPaymentSuccessWithoutUrlParams()
    {
        $this->loginUser();
        
        $purchase = $this->findEntity(PurchaseRepository::class, ['ref' => 'valid_purchase']);
        $this->client->request('GET', $this->urlGenerator->generate('purchase_create', ['id' => $purchase->getId()]));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    // le reste ne peut être testé qu'en endToEnd ( car nécessite validation du paiement par stripe )
    

}