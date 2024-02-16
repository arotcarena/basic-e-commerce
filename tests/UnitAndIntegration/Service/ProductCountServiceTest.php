<?php
namespace App\Tests\UnitAndIntegration\Service;

use App\DataFixtures\Tests\PurchaseTestFixtures;
use App\Repository\ProductRepository;
use App\Repository\PurchaseRepository;
use App\Service\ProductCountService;
use App\Tests\Utils\FixturesTrait;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @group Service
 */
class ProductCountServiceTest extends KernelTestCase
{
    use FixturesTrait;

    private ProductCountService $productCountService;

    private EntityManagerInterface $em;

    public function setUp(): void 
    {
        parent::setUp();

        self::bootKernel();

        $this->loadFixtures([PurchaseTestFixtures::class]);

        $this->productCountService = static::getContainer()->get(ProductCountService::class);

        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }

    //countSales
    public function testCountSalesWithProductHavingNoSales()
    {
        $purchase = $this->findEntity(PurchaseRepository::class, ['ref' => 'valid_purchase']); // purchase avec un product à 0 sales
        $productArray = $purchase->getPurchaseLines()->get(0)->getProduct();
        $product = $this->findEntity(ProductRepository::class, ['id' => $productArray['id']]);
        $quantity = $purchase->getPurchaseLines()->get(0)->getQuantity();
        $this->assertNull($product->getCountSales(), 'le test est faussé car le product a déjà des sales');
        $productId = $product->getId();

        $this->productCountService->countSales($purchase);

        $updatedProduct = $this->findEntity(ProductRepository::class, ['id' => $productId]);
        $this->assertEquals(
            $quantity, $updatedProduct->getCountSales()
        );
    } 
    public function testCountSalesWithProductHavingSales()
    {
        $purchase = $this->findEntity(PurchaseRepository::class, ['ref' => 'valid_purchase']); // purchase avec un product à 0 sales
        $productArray = $purchase->getPurchaseLines()->get(0)->getProduct();
        $product = $this->findEntity(ProductRepository::class, ['id' => $productArray['id']]);
        $productId = $product->getId();
        $quantity = $purchase->getPurchaseLines()->get(0)->getQuantity();
        //on ajoute des sales au product
        $product->setCountSales(10);
        $this->em->flush();
        //on vérifie que c'est bien persisté
        $updatedProduct = $this->findEntity(ProductRepository::class, ['id' => $productId]);
        $this->assertEquals(
            10, $updatedProduct->getCountSales()
        );

        //on utilise countService
        $this->productCountService->countSales($purchase);

        //on vérifie le nouveau countSales
        $reUpdatedProduct = $this->findEntity(ProductRepository::class, ['id' => $productId]);
        $this->assertEquals(
            $quantity + 10, $reUpdatedProduct->getCountSales()
        );
    } 
    public function testCountSalesWithTwoProducts()
    {
        $purchase = $this->findEntity(PurchaseRepository::class, ['ref' => 'purchase_test_one_product_over_stock']); // purchase avec 2 product à 0 sales
        $product1Array = $purchase->getPurchaseLines()->get(0)->getProduct();
        $product1 = $this->findEntity(ProductRepository::class, ['id' => $product1Array['id']]);
        $product2Array = $purchase->getPurchaseLines()->get(1)->getProduct();
        $product2 = $this->findEntity(ProductRepository::class, ['id' => $product2Array['id']]);


        $quantity1 = $purchase->getPurchaseLines()->get(0)->getQuantity();
        $quantity2 = $purchase->getPurchaseLines()->get(1)->getQuantity();

        $this->assertNull($product1->getCountSales(), 'le test est faussé car le product 1 a déjà des sales');
        $product1Id = $product1->getId();
        
        $this->assertNull($product2->getCountSales(), 'le test est faussé car le product 2 a déjà des sales');
        $product2Id = $product2->getId();

        $this->productCountService->countSales($purchase);

        $updatedProduct1 = $this->findEntity(ProductRepository::class, ['id' => $product1Id]);
        $this->assertEquals(
            $quantity1, $updatedProduct1->getCountSales()
        );
        $updatedProduct2 = $this->findEntity(ProductRepository::class, ['id' => $product2Id]);
        $this->assertEquals(
            $quantity2, $updatedProduct2->getCountSales()
        );
    }



}