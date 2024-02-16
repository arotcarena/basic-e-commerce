<?php
namespace App\Tests\UnitAndIntegration\Repository;

use App\Entity\Cart;
use DateTimeImmutable;
use App\Entity\Product;
use App\Entity\Purchase;
use App\Config\SiteConfig;
use App\Entity\PurchaseLine;
use Doctrine\ORM\EntityManager;
use App\Tests\Utils\FixturesTrait;
use App\Repository\ProductRepository;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\Admin\DataModel\PurchaseFilter;
use Symfony\Component\HttpFoundation\Request;
use App\DataFixtures\Tests\PurchaseTestFixtures;
use App\DataFixtures\Tests\PurchaseStatusTestFixtures;
use App\Entity\CartLine;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group Repository
 */
class PurchaseRepositoryTest extends KernelTestCase
{
    use FixturesTrait;

    private PurchaseRepository $purchaseRepository;

    public function setUp(): void
    {
        $this->purchaseRepository = static::getContainer()->get(PurchaseRepository::class);
        $this->loadFixtures([PurchaseStatusTestFixtures::class]);
    }

    public function testFindPurchaseProductsReturnProductsIndexedByProductId()
    {
        $this->loadFixtures([PurchaseTestFixtures::class]);
        $purchase = $this->findEntity(PurchaseRepository::class, ['ref' => 'purchase_test_one_zero_stock_and_one_ok']); // purchase avec 2 products
        $products = $this->purchaseRepository->findPurchaseProducts($purchase->getId());
        foreach($products as $key => $product)
        {
            $this->assertInstanceOf(Product::class, $product);
            $this->assertEquals($key, $product->getId());
        }
    }

    public function testFindPurchaseProductsReturnCorrectProducts()
    {
        $this->loadFixtures([PurchaseTestFixtures::class]);
        $purchase = $this->findEntity(PurchaseRepository::class, ['ref' => 'purchase_test_one_zero_stock_and_one_ok']); // purchase avec 2 products
        $productArray1 = $purchase->getPurchaseLines()->get(0)->getProduct();
        $productArray2 = $purchase->getPurchaseLines()->get(1)->getProduct();

        $products = $this->purchaseRepository->findPurchaseProducts($purchase->getId());
        $this->assertCount(2, $products);
        $this->assertEquals($productArray1['id'], $products[$productArray1['id']]->getId());
        $this->assertEquals($productArray2['publicRef'], $products[$productArray2['id']]->getPublicRef());
    }

    public function testCountPurchasesInProcess()
    {
        $this->assertEquals(
            15, 
            $this->purchaseRepository->count([]),
            'le test est faussé : on ne trouve pas le bon nombre de fixtures au total'
        );
        $this->assertEquals(
            10,
            $this->purchaseRepository->countPurchasesInProcess()
        );
    }

    public function testAdminFilterStatus()
    {
        $pagination = $this->purchaseRepository->adminFilter(
            new Request,
            (new PurchaseFilter)->setStatus(SiteConfig::STATUS_SENT),
            100
        );
        $this->assertCount(5, $pagination->getItems());
        foreach($pagination->getItems() as $purchase)
        {
            $this->assertEquals(SiteConfig::STATUS_SENT, $purchase->getStatus());
        }
    }
    public function testAdminFilterSortByCreatedAtASC()
    {
        $pagination = $this->purchaseRepository->adminFilter(
            new Request,
            (new PurchaseFilter)->setSortBy('createdAt_ASC'),
            100
        );
        /** @var Purchase[] */
        $purchases = $pagination->getItems();

        $previousTimestamp = 0;
        for ($i=0; $i < 7; $i++) { 
            $currentTimestamp = $purchases[$i]->getCreatedAt()->getTimestamp();
            $this->assertTrue($currentTimestamp > $previousTimestamp);
            $previousTimestamp = $currentTimestamp;
        }
    }
    public function testAdminFilterSortByCreatedAtDESC()
    {
        $pagination = $this->purchaseRepository->adminFilter(
            new Request,
            (new PurchaseFilter)->setSortBy('createdAt_DESC'),
            100
        );
        /** @var Purchase[] */
        $purchases = $pagination->getItems();

        $previousTimestamp = time();
        for ($i=0; $i < 7; $i++) { 
            $currentTimestamp = $purchases[$i]->getCreatedAt()->getTimestamp();
            $this->assertTrue($currentTimestamp < $previousTimestamp);
            $previousTimestamp = $currentTimestamp;
        }
    }

    //findDuplicate
    public function testFindDuplicateWithSameUserSameTotalAndStatusNotPendingButNotSameLines()
    {
        $this->loadFixtures([PurchaseTestFixtures::class]);
        $product = $this->findEntity(ProductRepository::class);

        $purchase = $this->findEntity(PurchaseRepository::class, ['ref' => 'purchase_test_duplicate']);  // purchase datant de moins de un jour, avec status paid
        $testCart = (new Cart)
                        ->setUser($purchase->getUser())
                        ->setTotalPrice($purchase->getTotalPrice())
                        ->addCartLine(
                            (new CartLine)
                            ->setProduct($product)
                            ->setQuantity(1050)
                            ->setTotalPrice(100)
                        )
                        ->setUpdatedAt(new DateTimeImmutable())
                        ;

        $this->assertNull($this->purchaseRepository->findDuplicateNotPendingPurchase($testCart, $purchase->getUser()));
    }

    public function testFindDuplicateWithSameUserSameTotalStatusPendingAndSameLines()
    {
        $this->loadFixtures([PurchaseTestFixtures::class]);
        $purchase = $this->findEntity(PurchaseRepository::class, ['ref' => 'purchase_test_duplicate_pending']);  // purchase datant de moins de un jour, avec status pending
        $testCart = (new Cart)
                        ->setUser($purchase->getUser())
                        ->setTotalPrice($purchase->getTotalPrice())
                        ->setUpdatedAt(new DateTimeImmutable())
                        ;
        foreach($purchase->getPurchaseLines() as $purchaseLine)
        {
            $product = $this->findEntity(ProductRepository::class, ['id' => $purchaseLine->getProduct()['id']]);
            $testCart
                ->addCartLine(
                    (new CartLine)
                    ->setProduct($product)
                    ->setQuantity($purchaseLine->getQuantity())
                    ->setTotalPrice($purchaseLine->getTotalPrice())
                );
        }

        $matchingPurchase = $this->purchaseRepository->findDuplicateNotPendingPurchase($testCart, $purchase->getUser());
        $this->assertNull($matchingPurchase);
    }

    public function testFindDuplicateWithSameUserSameTotalStatusNotPendingAndSameLines()
    {
        $this->loadFixtures([PurchaseTestFixtures::class]);
        $purchase = $this->findEntity(PurchaseRepository::class, ['ref' => 'purchase_test_duplicate']);  // purchase datant de moins de un jour, avec status paid
        $testCart = (new Cart)
                        ->setUser($purchase->getUser())
                        ->setTotalPrice($purchase->getTotalPrice())
                        ->setUpdatedAt(new DateTimeImmutable())
                        ;
        foreach($purchase->getPurchaseLines() as $purchaseLine)
        {
            $product = $this->findEntity(ProductRepository::class, ['id' => $purchaseLine->getProduct()['id']]);
            $testCart
                ->addCartLine(
                    (new CartLine)
                    ->setProduct($product)
                    ->setQuantity($purchaseLine->getQuantity())
                    ->setTotalPrice($purchaseLine->getTotalPrice())
                );
        }

        $matchingPurchase = $this->purchaseRepository->findDuplicateNotPendingPurchase($testCart, $purchase->getUser());
        $this->assertNotNull($matchingPurchase);
        $this->assertEquals($purchase->getId(), $matchingPurchase->getId());
    }
    
}