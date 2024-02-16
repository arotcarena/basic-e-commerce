<?php
namespace App\Tests\UnitAndIntegration\Repository;

use App\Entity\Picture;
use App\Entity\Product;
use App\Tests\Utils\FixturesTrait;
use App\Repository\ProductRepository;
use App\DataFixtures\Tests\ProductTestFixtures;
use App\DataFixtures\Tests\ProductWithOrWithoutStockTestFixtures;
use App\Form\Admin\DataModel\ProductFilter;
use App\Form\DataModel\SearchParams;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group Repository
 */
class ProductRepositoryTest extends KernelTestCase
{
    use FixturesTrait;

    private ProductRepository $productRepository;

    public function setUp(): void 
    {
        parent::setUp();

        self::bootKernel();

        $this->productRepository = static::getContainer()->get(ProductRepository::class);
        $this->loadFixtures([ProductTestFixtures::class]);
    }
    //QSEARCH
    public function testQSearchReturnArrayOfProducts()
    {
        $products = $this->productRepository->qSearch('e');
        $this->assertInstanceOf(
            Product::class,
            $products[0]
        );
    }
    public function testQSearchReturnOnlyResultsContainingEntireQ()
    {
        $products = $this->productRepository->qSearch('on obj');
        foreach($products as $product)
        {
            $this->assertFalse($product->getDesignation() === 'objet');
        }
    }
    public function testQSearchLookIntoDesignation()
    {
        $products = $this->productRepository->qSearch('objet');
        $this->assertCount(2, $products);
    }
    public function testQSearchLookIntoCategoryName()
    {
        $product = $this->productRepository->findOneBy([]);
        $categoryName = $product->getCategory()->getName();
        $q = substr($categoryName, 0, 3);

        $products = $this->productRepository->qSearch($q);
        $bool = false;
        foreach($products as $product)
        {
            if(
                !str_contains($product->getDesignation(), $q) && 
                !str_contains($product->getSubCategory()->getName(), $q) && 
                str_contains($product->getCategory()->getName(), $q)
            )
            {
                $bool = true;
            }
        }
        $this->assertTrue($bool, 'peut-être que le produit a aussi le q dans un autre champ que le nom de la catégorie. essayer de rejouer le test');
    }
    public function testQSearchLookIntoSubCategoryName()
    {
        $product = $this->productRepository->findOneBy([]);
        $subCategoryName = $product->getSubCategory()->getName();
        $q = substr($subCategoryName, 0, 3);

        $products = $this->productRepository->qSearch($q);
        $bool = false;
        foreach($products as $product)
        {
            if(
                !str_contains($product->getDesignation(), $q) && 
                !str_contains($product->getCategory()->getName(), $q) && 
                str_contains($product->getSubCategory()->getName(), $q)
            )
            {
                $bool = true;
            }
        }
        $this->assertTrue($bool, 'peut-être que le produit a aussi le q dans un autre champ que le nom de la sous-catégorie. essayer de rejouer le test');
    }
    public function testCountQSearchCorrectCount()
    {
        $count = $this->productRepository->countQSearch('mon objet');
        $this->assertEquals(1, $count);
        
        $count = $this->productRepository->countQSearch('objet');
        $this->assertEquals(2, $count);

        $this->loadFixtures([ProductWithOrWithoutStockTestFixtures::class]);
        $count = $this->productRepository->countQSearch('stock test');
        $this->assertEquals(1, $count);
    }
    public function testQSearchApplyCorrectLimit()
    {
        $products = $this->productRepository->qSearch('obj', 1);
        $this->assertCount(
            1, 
            $products
        );
    }
    public function testQSearchReturnProductWithFirstPicture()
    {
        $products = $this->productRepository->qSearch('obj', 1);
        $this->assertInstanceOf(
            Picture::class,
            $products[0]->getFirstPicture()
        );
    }
    public function testQSearchReturnProductWithCorrectFirstPicture()
    {
        $products = $this->productRepository->qSearch('obj', 4);
        
        foreach($products as $product)
        {
            $this->assertEquals(
                $product->getId(), 
                $product->getFirstPicture()->getProduct()->getId(),
                'la firstPicture placée dans product n\'appartient pas à ce product'
            );
            $this->assertEquals(
                1, 
                $product->getFirstPicture()->getListPosition(),
                'la firstPicture placée dans product a un listPosition != de 1'
            );
        }
    }
    public function testQSearchSelectOnlyProductsInStock()
    {
        $this->loadFixtures([ProductWithOrWithoutStockTestFixtures::class]);
        $products = $this->productRepository->qSearch('stock test', 4);
        $this->assertCount(1, $products);
        $this->assertTrue($products[0]->getStock() > 0);
    }
    //FILTER
    public function testFilterPrice()
    {
        $products = $this->productRepository->filter(
            (new SearchParams)->setMaxPrice(1500)
        );
        $this->assertCount(1, $products);
        $this->assertLessThan(1500, $products[0]->getPrice());

        $products = $this->productRepository->filter(
            (new SearchParams)->setMinPrice(30000)
        );
        $this->assertCount(1, $products);
        $this->assertTrue($products[0]->getPrice() > 30000);
    }
    public function testFilterPriceDontIgnoreValueZero()
    {
        $products = $this->productRepository->filter(
            (new SearchParams)->setMaxPrice(0)
        );
        $this->assertCount(0, $products);
    }
    public function testFilterQ()
    {
        $products = $this->productRepository->filter(
            (new SearchParams)->setQ('on obj')
        );
        $this->assertCount(1, $products);
        $this->assertEquals('mon-objet', $products[0]->getSlug());
    }
    public function testFilterCategory()
    {
        $product = $this->productRepository->findOneBy([]);
        $category = $product->getCategory();
        /*il faut supprimer les products dont le stock = 0 car ils ne seront pas retournés par le repository */
        $expectedProducts = [];
        foreach($category->getProducts() as $p)
        {
            if($p->getStock() > 0) 
            {
                $expectedProducts[] = $p;
            }
        } 

        $returnProducts = $this->productRepository->filter(
            (new SearchParams)->setCategoryId($category->getId())
        );
        $this->assertEquals(count($expectedProducts), count($returnProducts));
        foreach($returnProducts as $returnProduct)
        {
            $this->assertEquals(
                $category->getId(),
                $returnProduct->getCategory()->getId()
            );
        }
    }
    public function testFilterWithEmptyQString()
    {
        $products = $this->productRepository->filter(
            (new SearchParams)->setQ('')
        );
        $this->assertTrue(count($products) > 0, 'devrait retourner des products même si qSearch = ""');
    }
    public function testFilterSort()
    {
        $products = $this->productRepository->filter(
            (new SearchParams)->setSort('price_ASC')
        );
        for ($i=0; $i < 5; $i++) 
        { 
            $this->assertTrue($products[$i]->getPrice() <= $products[$i+1]->getPrice());
        }

        $products = $this->productRepository->filter(
            (new SearchParams)->setSort('createdAt_DESC')
        );
        for ($i=0; $i < 5; $i++) 
        { 
            $this->assertTrue(
                $products[$i]->getCreatedAt()->format('Y:m:d H:i:s') >= 
                $products[$i+1]->getCreatedAt()->format('Y:m:d H:i:s')
            );
        }
    }
    public function testFilterSelectOnlyProductsInStock()
    {
        $this->loadFixtures([ProductWithOrWithoutStockTestFixtures::class]);
        $products = $this->productRepository->filter((new SearchParams)->setQ('stock test'));
        $this->assertCount(1, $products);
        $this->assertTrue($products[0]->getStock() > 0);
    }
    public function testCountFilterCorrectCount()
    {
        $count = $this->productRepository->countFilter(
            (new SearchParams)->setMaxPrice(1500)
        );
        $this->assertEquals(1, $count);

        $this->loadFixtures([ProductWithOrWithoutStockTestFixtures::class]);
        $count = $this->productRepository->countFilter(
            (new SearchParams)->setQ('stock test')
        );
        $this->assertEquals(1, $count);
    }
    //adminFilter
    public function testAdminFilterPrice()
    {
        $pagination = $this->productRepository->adminFilter(
            new Request,
            (new ProductFilter)->setMinPrice(30000)
        );
        $this->assertCount(1, $pagination->getItems());
        $this->assertTrue($pagination->getItems()[0]->getPrice() > 30000);
    }
    public function testAdminFilterDontIgnoreValueZero()
    {
        $pagination = $this->productRepository->adminFilter(
            new Request,
            (new ProductFilter)->setMaxPrice(0)  // 0 products avec price = 0
        );
        $this->assertCount(0, $pagination->getItems());
        $pagination = $this->productRepository->adminFilter(
            new Request,
            (new ProductFilter)->setMaxStock(0)  // 2 products avec stock = 0
        );
        $this->assertCount(2, $pagination->getItems());
    }
    public function testAdminFilterDontIgnoreProductsWithNoStock()
    {
        $this->loadFixtures([ProductWithOrWithoutStockTestFixtures::class]);
        $pagination = $this->productRepository->adminFilter(
            new Request,
            (new ProductFilter)->setQ('stock test')
        );
        $this->assertCount(2, $pagination->getItems());
    }
    public function testAdminFilterStock()
    {
        $pagination = $this->productRepository->adminFilter(
            new Request,
            (new ProductFilter)->setMaxStock(50)  // 7 products avec stock = 50 ou = 0 dans la fixtures
        );
        $this->assertCount(7, $pagination->getItems());
        $pagination = $this->productRepository->adminFilter(
            new Request,
            (new ProductFilter)->setMinStock(90)  // 5 products avec stock > 50 ( = 100) dans la fixtures
        );
        $this->assertCount(5, $pagination->getItems());
    }
    public function testAdminFilterQ()
    {
        $pagination = $this->productRepository->adminFilter(
            new Request,
            (new ProductFilter)->setQ('on obj')
        );
        $this->assertCount(1, $pagination->getItems());
        $this->assertEquals('mon-objet', $pagination->getItems()[0]->getSlug());
    }
    public function testAdminFilterCategory()
    {
        $product = $this->productRepository->findOneBy([]);
        $category = $product->getCategory();
        $expectedProducts = $category->getProducts();

        $pagination = $this->productRepository->adminFilter(
            new Request,
            (new ProductFilter)->setCategory($category)
        );
        $this->assertEquals(count($expectedProducts), count($pagination->getItems()));
        foreach($pagination->getItems() as $returnProduct)
        {
            $this->assertEquals(
                $category->getId(),
                $returnProduct->getCategory()->getId()
            );
        }
    }
    public function testAdminFilterWithEmptyQString()
    {
        $pagination = $this->productRepository->adminFilter(
            new Request,
            (new ProductFilter)->setQ('')
        );
        $this->assertTrue(count($pagination->getItems()) > 0, 'devrait retourner des products même si qSearch = ""');
    }
    public function testAdminFilterSort()
    {
        $pagination = $this->productRepository->adminFilter(
            new Request,
            (new ProductFilter)->setSortBy('price_ASC')
        );
        for ($i=0; $i < 5; $i++) 
        { 
            $this->assertTrue($pagination->getItems()[$i]->getPrice() <= $pagination->getItems()[$i+1]->getPrice());
        }

        $pagination = $this->productRepository->adminFilter(
            new Request,
            (new ProductFilter)->setSortBy('createdAt_DESC')
        );
        for ($i=0; $i < 5; $i++) 
        { 
            $this->assertTrue(
                $pagination->getItems()[$i]->getCreatedAt()->format('Y:m:d H:i:s') >= 
                $pagination->getItems()[$i+1]->getCreatedAt()->format('Y:m:d H:i:s')
            );
        }
    }

    //findOneByPublicRef
    public function testFindOneByPublicRefReturnCorrectProduct()
    {
        $product = $this->productRepository->findOneByPublicRef('publicRef');
        $this->assertEquals(
            'public-ref-test',
            $product->getSlug()
        );
    }
    public function testFindOneByPublicRefReturnProductHydratedWithCorrectFirstPicture()
    {
        /** @var Product */
        $product = $this->productRepository->findOneByPublicRef('publicRef');
        $this->assertEquals(
            1,
            $product->getFirstPicture()->getListPosition()
        );
        $this->assertEquals(
            $product->getId(),
            $product->getFirstPicture()->getProduct()->getId()
        );
    }
    //findByIdGroup
    public function testFindByIdGroupReturnCorrectProducts()
    {
        //il y a 7 products
        $products = $this->productRepository->findAll();
        $ids = [];
        $sentProducts = [];
        for($i=0; $i<5; $i++)
        {
            $ids[] = $products[$i]->getId();
            $sentProducts[$products[$i]->getId()] = $products[$i];
        }

        $return = $this->productRepository->findByIdGroup($ids);
        $returnProducts = [];
        foreach($return as $returnProduct)
        {
            $returnProducts[$returnProduct->getId()] = $returnProduct;
        }

        $this->assertEquals(
            count($ids),
            count($return)
        );
        foreach($sentProducts as $sentProduct)
        {
            $this->assertEquals(
                $sentProduct->getDesignation(),
                $returnProducts[$sentProduct->getId()]->getDesignation()
            );
        }
    }
}