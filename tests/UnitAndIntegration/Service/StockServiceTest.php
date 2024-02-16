<?php
namespace App\Tests\UnitAndIntegration\Service;

use App\Service\CartService;
use App\Service\StockService;
use App\Tests\Utils\FixturesTrait;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PurchaseLineRepository;
use PHPUnit\Framework\MockObject\MockObject;
use App\DataFixtures\Tests\PurchaseTestFixtures;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group Service
 */
class StockServiceTest extends KernelTestCase
{
    use FixturesTrait;
    
    private StockService $stockService;

    private EntityManagerInterface $em;

    private MockObject|EntityManagerInterface $emMock;

    public function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $this->loadFixtures([PurchaseTestFixtures::class]);

        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $purchaseRepository = static::getContainer()->get(PurchaseRepository::class);

        $this->emMock = $this->createMock(EntityManagerInterface::class);

        $this->stockService = new StockService($this->emMock, $purchaseRepository);
    }

    public function testVerifyAndUpdateStocksWithPurchaseContainingProductThatHaveBeenRemoved()
    {
        $purchase = $this->findEntity(PurchaseRepository::class, ['ref' => 'valid_purchase_with_two_products']); // 2 products
        // on supprime l'un des deux products
        $productId = $purchase->getPurchaseLines()->get(0)->getProduct()['id'];
        $product = $this->findEntity(ProductRepository::class, ['id' => $productId]);
        $this->em->remove($product);
        $this->em->flush();

        $this->emMock->expects($this->never())
                    ->method('flush')
                    ;
        $result = $this->stockService->verifyPurchaseStocksAndPriceAndUpdateStocksIfOk($purchase);
        $this->assertFalse($result);
    }

    public function testVerifyAndUpdateStocksWithPurchaseContainingProductOverStock()
    {
        $purchase = $this->findEntity(PurchaseRepository::class, ['ref' => 'purchase_test_one_product_over_stock']); // 2 products dont un overstock

        $this->emMock->expects($this->never())
                    ->method('flush')
                    ;
        $result = $this->stockService->verifyPurchaseStocksAndPriceAndUpdateStocksIfOk($purchase);
        $this->assertFalse($result);
    }

    public function testVerifyAndUpdateStocksWithPurchaseContainingProductWhosePriceHasChanged()
    {
        $purchase = $this->findEntity(PurchaseRepository::class, ['ref' => 'purchase_test_one_product_over_stock']); // 2 products dont un overstock
        // on modifie le prix d'un des deux products
        $productId = $purchase->getPurchaseLines()->get(0)->getProduct()['id'];
        $product = $this->findEntity(ProductRepository::class, ['id' => $productId]);
        $product->setPrice($product->getPrice() + 10);
        $this->em->flush();

        $this->emMock->expects($this->never())
                    ->method('flush')
                    ;
        $result = $this->stockService->verifyPurchaseStocksAndPriceAndUpdateStocksIfOk($purchase);
        $this->assertFalse($result);
    }
    public function testVerifyAndUpdateStocksWithValidPurchase()
    {
        $purchase = $this->findEntity(PurchaseRepository::class, ['ref' => 'valid_purchase_with_two_products']); // 2 products

        $this->emMock->expects($this->once())
                    ->method('flush')
                    ;
        $result = $this->stockService->verifyPurchaseStocksAndPriceAndUpdateStocksIfOk($purchase);
        $this->assertTrue($result);
    }
    public function testVerifyAndUpdateStocksCorrectUpdateStocks()
    {
        $purchase = $this->findEntity(PurchaseRepository::class, ['ref' => 'valid_purchase_with_two_products']); // 2 products --> qté 3 = stock 100 / qté 1 = stock 1
        
        $quantity1 = $purchase->getPurchaseLines()->get(0)->getQuantity();
        $productId1 = $purchase->getPurchaseLines()->get(0)->getProduct()['id'];
        $stock1 = $this->findEntity(ProductRepository::class, ['id' => $productId1])->getStock();

        $quantity2 = $purchase->getPurchaseLines()->get(1)->getQuantity();
        $productId2 = $purchase->getPurchaseLines()->get(1)->getProduct()['id'];
        $stock2 = $this->findEntity(ProductRepository::class, ['id' => $productId2])->getStock();

        $this->stockService->verifyPurchaseStocksAndPriceAndUpdateStocksIfOk($purchase);

        $updatedStock1 = $this->findEntity(ProductRepository::class, ['id' => $productId1])->getStock();
        $updatedStock2 = $this->findEntity(ProductRepository::class, ['id' => $productId2])->getStock();

        $this->assertEquals($stock1 - $quantity1, $updatedStock1);
        $this->assertEquals($stock2 - $quantity2, $updatedStock2);
    }

}