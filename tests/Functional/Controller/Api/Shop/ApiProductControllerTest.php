<?php
namespace App\Tests\Functional\Controller\Api\Shop;

use App\Convertor\ProductToArrayConvertor;
use App\DataFixtures\Tests\CategoryTestFixtures;
use App\DataFixtures\Tests\ProductTestFixtures;
use App\DataFixtures\Tests\ProductWithOrWithoutCategoryTestFixtures;
use App\Form\DataModel\SearchParams;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Tests\Functional\FunctionalTest;
use Doctrine\Bundle\DoctrineBundle\DataCollector\DoctrineDataCollector;



/**
 * @group FunctionalApi
 */
class ApiProductControllerTest extends FunctionalTest
{
    private ProductRepository $productRepository;

    private ProductToArrayConvertor $productConvertor;


    public function setUp(): void
    {
        parent::setUp();

        $this->productRepository = static::getContainer()->get(ProductRepository::class);
        $this->productConvertor = static::getContainer()->get(ProductToArrayConvertor::class);
        $this->loadFixtures([ProductTestFixtures::class]);
    }
    //SEARCH
    public function testSearchWithEmptyQStringReturnZeroProducts()
    {
        $this->client->request('GET', $this->urlGenerator->generate('api_product_search'), [
            'q' => ''
        ]);
        $this->assertCount(
            0,
            json_decode($this->client->getResponse()->getContent())->products
        );
        $this->assertEquals(
            0,
            json_decode($this->client->getResponse()->getContent())->count
        );
    }
    public function testSearchReturnCorrectProducts()
    {
        $products = $this->productRepository->qSearch('obj', 4);
        $expectedResult = json_encode($this->productConvertor->convert($products));

        $this->client->request('GET', $this->urlGenerator->generate('api_product_search'), [
            'q' => 'obj',
            'limit' => 4
        ]);
        $result = $this->client->getResponse()->getContent();
        $this->assertEquals(json_decode($expectedResult), json_decode($result)->products);
    }
    public function testSearchApplyCorrectLimit()
    {
        $products = $this->productRepository->qSearch('obj', 4);
        $this->assertCount(2, $products, 'problème de fixtures : il devrait y avoir 2 produits correspondant au q "obj". Le test de limit est donc faussé');
        $this->client->request('GET', $this->urlGenerator->generate('api_product_search'), [
            'q' => 'obj',
            'limit' => 1
        ]);
        $this->assertCount(
            1, 
            json_decode($this->client->getResponse()->getContent())->products
        );
    }
    public function testSearchReturnCorrectCount()
    {
        $count = $this->productRepository->countQSearch('obj');
        $this->client->request('GET', $this->urlGenerator->generate('api_product_search'), [
            'q' => 'obj',
        ]);
        $this->assertEquals(
            $count, 
            json_decode($this->client->getResponse()->getContent())->count
        );
    }
    public function testSearchDatabaseQueriesCount()
    {
        $this->client->enableProfiler();

        $this->client->request('GET', $this->urlGenerator->generate('api_product_search'), [
            'q' => 'obj'
        ]);

        /** @var DoctrineDataCollector */
        $dbCollector = $this->client->getProfile()->getCollector('db');

        $this->assertEquals(3, $dbCollector->getQueryCount(), 'le pb peut venir de ProductRepository');
    }
    //INDEX
    public function testIndexCorrectCountProducts()
    {
        $this->client->request('GET', $this->urlGenerator->generate('api_product_index'), [
            'q' => 'obj'
        ]);
        $result = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals($result->count, count($result->products));
    }
    public function testIndexFilters()
    {
        $products = $this->productRepository->filter(
            (new SearchParams)
            ->setQ('obj')
            ->setMaxPrice(1500)
        );
        $this->client->request('GET', $this->urlGenerator->generate('api_product_index'), [
            'q' => 'obj',
            'maxPrice' => 1500
        ]);
        $returnProducts = json_decode($this->client->getResponse()->getContent())->products;
        $this->assertEquals(count($products), count($returnProducts));
        $this->assertEquals($products[0]->getId(), $returnProducts[0]->id);
        $this->assertTrue($returnProducts[0]->price < 1500);
    }
    public function testIndexFilterCategory()
    {
        $this->loadFixtures([ProductWithOrWithoutCategoryTestFixtures::class]);
        $productsTest = $this->productRepository->findAll();
        $category = $productsTest[0]->getCategory();
        $category1 = $productsTest[1]->getCategory();

        $this->client->request('GET', $this->urlGenerator->generate('api_product_index'), [
            'categoryId' => $category->getId()
        ]);
        $returnProducts = json_decode($this->client->getResponse()->getContent())->products;
        $this->assertCount(count($category->getProducts()), $returnProducts);

        $this->client->request('GET', $this->urlGenerator->generate('api_product_index'), [
            'categoryId' => $category1->getId()
        ]);
        $returnProducts = json_decode($this->client->getResponse()->getContent())->products;
        $this->assertCount(count($category1->getProducts()), $returnProducts);
    }
    public function testIndexFilterSubCategory()
    {
        $productsTest = $this->productRepository->findAll();
        $subCategory = $productsTest[0]->getSubCategory();
        $subCategory1 = $productsTest[3]->getSubCategory();

        $this->client->request('GET', $this->urlGenerator->generate('api_product_index'), [
            'subCategoryId' => $subCategory->getId()
        ]);
        $returnProducts = json_decode($this->client->getResponse()->getContent())->products;
        $this->assertCount(count($subCategory->getProducts()), $returnProducts);

        $this->client->request('GET', $this->urlGenerator->generate('api_product_index'), [
            'subCategoryId' => $subCategory1->getId()
        ]);
        $returnProducts = json_decode($this->client->getResponse()->getContent())->products;
        $this->assertCount(count($subCategory1->getProducts()), $returnProducts);
    }
    public function testIndexSort()
    {
        $this->client->request('GET', $this->urlGenerator->generate('api_product_index'), [
            'sort' => 'price_ASC'
        ]);
        $returnProducts = json_decode($this->client->getResponse()->getContent())->products;
        for ($i=0; $i < 5; $i++) { 
            $this->assertTrue((int)$returnProducts[$i]->price <= (int)$returnProducts[$i + 1]->price);
        }
    }
    public function testIndexDatabaseQueriesCount()
    {
        $this->client->enableProfiler();
        
        $this->client->request('GET', $this->urlGenerator->generate('api_product_index'));

        /** @var DoctrineDataCollector */
        $dbCollector = $this->client->getProfile()->getCollector('db');
        $this->assertLessThan(5, $dbCollector->getQueryCount());
    }
    //TEST PRODUCT 
}