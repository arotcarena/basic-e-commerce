<?php
namespace App\Tests\Functional\Controller\Shop;

use stdClass;
use App\Entity\Product;
use App\Config\TextConfig;
use App\Service\CartService;
use App\Tests\Utils\FixturesTrait;
use App\Repository\ProductRepository;
use App\Tests\Functional\FunctionalTest;
use Symfony\Component\HttpFoundation\Response;
use App\DataFixtures\Tests\ProductTestFixtures;
use App\DataFixtures\Tests\UserTestFixtures;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Doctrine\Bundle\DoctrineBundle\DataCollector\DoctrineDataCollector;



/**
 * @group FunctionalApi
 */
class ApiCartControllerTest extends FunctionalTest
{
    use FixturesTrait;

    private CartService $cartService;

    public function setUp(): void 
    {
        parent::setUp();

        $this->cartService = $this->client->getContainer()->get(CartService::class);
    }

    public function testAddWithNotNumericProductIdValue()
    {
        $this->expectException(InvalidParameterException::class);
        $this->callAdd('badtype', 1);
    }
    public function testLessWithNotNumericProductIdValue()
    {
        $this->expectException(InvalidParameterException::class);
        $this->callLess('badtype', 1);
    }
    public function testRemoveWithNotNumericProductIdValue()
    {
        $this->expectException(InvalidParameterException::class);
        $this->callRemove('badtype');
    }
    public function testAddWithInexistantProductIdValue()
    {
        $this->callAdd(123456789, 1);
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    public function testLessWithInexistantProductIdValue()
    {
        $this->callLess(123456789, 1);
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    public function testRemoveWithInexistantProductIdValue()
    {
        $this->callRemove(123456789);
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    /**
     * Un seul test pour getFullCart car les autres fonctions l'utilisent aussi donc s'il ne fonctionne pas correctement on le verra de suite 
     */
    public function testGetFullCart()
    {
        $cart = $this->getFullCartResult();
        $this->assertEquals(
            ['id', 'cartLines', 'totalPrice', 'count', 'updatedAt'],
            array_keys(get_object_vars($cart))
        );
    }
    public function testAddProduct()
    {
        $this->loadFixtures([ProductTestFixtures::class]);
        $product = $this->findEntity(ProductRepository::class);

        $this->callAdd($product->getId(), 2);
        $cart = $this->getFullCartResult();

        $this->assertCount(1, $cart->cartLines);
        $this->assertEquals(
            $product->getId(),
            $cart->cartLines[0]->product->id
        );
        $this->assertEquals(
            2,
            $cart->cartLines[0]->quantity
        );
    }
    public function testAddProductCountCart()
    {
        $this->loadFixtures([ProductTestFixtures::class]);
        $product = $this->findEntity(ProductRepository::class, ['designation' => 'objet']);  // product avec countCart = null

        $this->callAdd($product->getId(), 2);
        $this->assertResponseIsSuccessful();

        /** @var Product */
        $product = $this->findEntity(ProductRepository::class, ['designation' => 'objet']);  // product avec countCart = null
        $this->assertEquals(1, $product->getCountCarts());

        // on vérifie qu'on ne peut faire qu'un ajout par session
        $this->callAdd($product->getId(), 2);
        $this->assertResponseIsSuccessful();

        /** @var Product */
        $product = $this->findEntity(ProductRepository::class, ['designation' => 'objet']);  // product avec countCart = null
        $this->assertEquals(1, $product->getCountCarts());
    }
    public function testAddSameProduct()
    {
        $this->loadFixtures([ProductTestFixtures::class]);
        $product = $this->findEntity(ProductRepository::class);
        $this->callAdd($product->getId(), 2);
        $this->callAdd($product->getId(), 3);

        $cart = $this->getFullCartResult();

        $this->assertCount(1, $cart->cartLines);
        $this->assertEquals(
            $product->getId(),
            $cart->cartLines[0]->product->id
        );
        $this->assertEquals(
            5,
            $cart->cartLines[0]->quantity
        );
    }
    public function testAddDifferentProducts()
    {
        $this->loadFixtures([ProductTestFixtures::class]);
        $products = $this->client->getContainer()->get(ProductRepository::class)->findAll();
        
        $this->callAdd($products[0]->getId(), 2);
        $this->callAdd($products[1]->getId(), 3);

        $cart = $this->getFullCartResult();

        $this->assertCount(2, $cart->cartLines);
        $this->assertEquals(
            $products[0]->getId(),
            $cart->cartLines[0]->product->id
        );
        $this->assertEquals(
            $products[1]->getId(),
            $cart->cartLines[1]->product->id
        );
        $this->assertEquals(
            2,
            $cart->cartLines[0]->quantity
        );
        $this->assertEquals(
            3,
            $cart->cartLines[1]->quantity
        );
    }
    public function testAddStockLimit()
    {
        $this->loadFixtures([ProductTestFixtures::class]);
        $product = $this->findEntity(ProductRepository::class);
        $this->callAdd($product->getId(), $product->getStock());
        $this->assertResponseIsSuccessful();
    }
    public function testAddOverStock()
    {
        $this->loadFixtures([ProductTestFixtures::class]);
        /** @var Product */
        $product = $this->findEntity(ProductRepository::class);
        $this->callAdd($product->getId(), $product->getStock() + 10);
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertEquals(
            TextConfig::ERROR_NOT_ENOUGH_STOCK,
            json_decode($this->client->getResponse()->getContent())->errors
        );
        $cart = $this->getFullCartResult();
        $this->assertEquals(
            $product->getId(),
            $cart->cartLines[0]->product->id
        );
        $this->assertEquals(
            $product->getStock(),
            $cart->cartLines[0]->quantity
        );
    }
    public function testAddQuantityResultsOverStock()
    {
        $this->loadFixtures([ProductTestFixtures::class]);
        /** @var Product */
        $product = $this->findEntity(ProductRepository::class);
        $this->callAdd($product->getId(), $product->getStock());
        $this->callAdd($product->getId(), 1);
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertEquals(
            TextConfig::ERROR_NOT_ENOUGH_STOCK,
            json_decode($this->client->getResponse()->getContent())->errors
        );

        $cart = $this->getFullCartResult();
        $this->assertEquals(
            $product->getId(),
            $cart->cartLines[0]->product->id
        );
        $this->assertEquals(
            $product->getStock(),
            $cart->cartLines[0]->quantity
        );
    }
    public function testRemoveProduct()
    {
        $this->loadFixtures([ProductTestFixtures::class]);
        $products = $this->client->getContainer()->get(ProductRepository::class)->findAll();
        
        $this->callAdd($products[0]->getId(), 2);
        $this->callAdd($products[1]->getId(), 3);

        $this->callRemove($products[1]->getId());

        $cart = $this->getFullCartResult();

        $this->assertCount(1, $cart->cartLines);
        $this->assertEquals(
            $products[0]->getId(),
            $cart->cartLines[0]->product->id
        );
        $this->assertEquals(
            2,
            $cart->cartLines[0]->quantity
        );
    }
    public function testLessProduct()
    {
        $this->loadFixtures([ProductTestFixtures::class]);
        $product = $this->findEntity(ProductRepository::class);
        $this->callAdd($product->getId(), 4);
        $this->callLess($product->getId(), 1);
        $cart = $this->getFullCartResult();
        $this->assertEquals(
            $product->getId(),
            $cart->cartLines[0]->product->id
        );
        $this->assertEquals(
            3,
            $cart->cartLines[0]->quantity
        );
    }
    public function testLessThanZeroProduct()
    {
        $this->loadFixtures([ProductTestFixtures::class]);
        $product = $this->findEntity(ProductRepository::class);
        $this->callAdd($product->getId(), 4);
        $this->callLess($product->getId(), 5);
        $cart = $this->getFullCartResult();
        $this->assertEquals(
            $product->getId(),
            $cart->cartLines[0]->product->id
        );
        $this->assertEquals(
            1,
            $cart->cartLines[0]->quantity
        );
    }
    public function testAddCountDatabaseQueriesWithNotLoggedUser()
    {
        $this->loadFixtures([ProductTestFixtures::class]);
        $products = $this->client->getContainer()->get(ProductRepository::class)->findAll();

        $this->client->enableProfiler();

        $this->callAdd($products[0]->getId(), 6);

        /** @var DoctrineDataCollector */
        $dbCollector = $this->client->getProfile()->getCollector('db');
        $this->assertLessThanOrEqual(4, $dbCollector->getQueryCount());   // une pour récupérer le product, et éventuellement + 3 pour ajouter un product.countCart et flush
    }
    public function testAddCountDatabaseQueriesWithLoggedUser()
    {
        $this->loadFixtures([UserTestFixtures::class]);
        $user = $this->findEntity(UserRepository::class);
        $this->client->loginUser($user);

        $this->loadFixtures([ProductTestFixtures::class]);
        $product = $this->findEntity(ProductRepository::class);

        $this->client->enableProfiler();
        $this->callAdd($product->getId(), 10);

        /** @var DoctrineDataCollector */
        $dbCollector = $this->client->getProfile()->getCollector('db');

        $this->assertLessThanOrEqual(6, $dbCollector->getQueryCount());
    }
    public function testGetFullCartCountDatabaseQueries()
    {
        $this->loadFixtures([ProductTestFixtures::class]);
        $products = $this->client->getContainer()->get(ProductRepository::class)->findAll();
        $this->callAdd($products[0]->getId(), 4);
        $this->callAdd($products[1]->getId(), 2);
        $this->callAdd($products[2]->getId(), 2);
        $this->callAdd($products[3]->getId(), 2);

        $this->client->enableProfiler();
        $this->client->request('GET', $this->urlGenerator->generate('api_cart_getFullCart'));

        /** @var DoctrineDataCollector */
        $dbCollector = $this->client->getProfile()->getCollector('db');
        $this->assertEquals(1, $dbCollector->getQueryCount());
    }
    public function testGetLightCart()
    {
        $lightCart = $this->getLightCartResult();
        $this->assertEquals(
            ['count', 'totalPrice'],
            array_keys(get_object_vars($lightCart))
        );
    }
    public function testGetLightCartReturnCorrectCount()
    {
        $this->loadFixtures([ProductTestFixtures::class]);
        $products = $this->client->getContainer()->get(ProductRepository::class)->findAll();
        $this->callAdd($products[0]->getId(), 4);
        $this->callAdd($products[1]->getId(), 2);

        $lightCart = $this->getLightCartResult();
        $this->assertEquals(
            6,
            $lightCart->count
        );
    }
    public function testGetLightCartReturnCorrectPrice()
    {
        $this->loadFixtures([ProductTestFixtures::class]);
        $products = $this->client->getContainer()->get(ProductRepository::class)->findAll();
        $this->callAdd($products[0]->getId(), 3);
        $this->callAdd($products[1]->getId(), 2);

        $lightCart = $this->getLightCartResult();
        $this->assertEquals(
            (3 * $products[0]->getPrice()) + (2 * $products[1]->getPrice()),
            $lightCart->totalPrice
        );
    }
    public function testCount()
    {
        $this->loadFixtures([ProductTestFixtures::class]);
        $products = $this->client->getContainer()->get(ProductRepository::class)->findAll();
        $this->callAdd($products[0]->getId(), 4);
        $this->callAdd($products[1]->getId(), 2);

        $this->client->request('GET', $this->urlGenerator->generate('api_cart_count'));
        $count = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(
            6,
            $count
        );
    }


    private function callLess($productId, $quantity)
    {
        $this->client->request('GET', $this->urlGenerator->generate('api_cart_less', [
            'productId' => $productId,
            'quantity' => $quantity
        ])); 
    }
    private function callRemove($productId)
    {
        $this->client->request('GET', $this->urlGenerator->generate('api_cart_remove', [
            'productId' => $productId
        ]));
    }
    private function callAdd($productId, $quantity)
    {
        $this->client->request('GET', $this->urlGenerator->generate('api_cart_add', [
            'productId' => $productId,
            'quantity' => $quantity
        ]));
    }
    private function getFullCartResult(): stdClass
    {
        $this->client->request('GET', $this->urlGenerator->generate('api_cart_getFullCart'));
        return json_decode($this->client->getResponse()->getContent());
    }
    private function getLightCartResult(): stdClass
    {
        $this->client->request('GET', $this->urlGenerator->generate('api_cart_getLightCart'));
        return json_decode($this->client->getResponse()->getContent());
    }

}