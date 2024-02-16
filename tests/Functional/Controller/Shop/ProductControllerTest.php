<?php 
namespace App\Tests\Functional\Controller\Shop;

use App\Entity\Product;
use App\Tests\Utils\FixturesTrait;
use App\Repository\ProductRepository;
use App\Tests\Functional\FunctionalTest;
use Symfony\Component\HttpFoundation\Response;
use App\DataFixtures\Tests\ProductTestFixtures;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use App\DataFixtures\Tests\ProductWithOrWithoutCategoryTestFixtures;
use Doctrine\Bundle\DoctrineBundle\DataCollector\DoctrineDataCollector;

/**
 * @group FunctionalShop
 */
class ProductControllerTest extends FunctionalTest
{
    use FixturesTrait;

    public function setUp(): void 
    {
        parent::setUp();

        $this->loadFixtures([ProductWithOrWithoutCategoryTestFixtures::class]);
    }

    //TEST SHOW
    public function testShowRouteWithOnlyProductSlugParam()
    {
        $product = $this->findEntity(ProductRepository::class, ['slug' => 'product-without-category-or-subcategory']);

        $this->client->request('GET', $this->urlGenerator->generate('product_show', [
            'slug' => $product->getSlug(),
            'publicRef' => $product->getPublicRef()
        ]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $product->getDesignation());
    }
    public function testShowRouteWithCategorySlug()
    {
        $productWithCategory = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-category']);

        $this->client->request('GET', $this->urlGenerator->generate('product_show_withCategory', [
            'slug' => $productWithCategory->getSlug(),
            'categorySlug' => $productWithCategory->getCategory()->getSlug(),
            'publicRef' => $productWithCategory->getPublicRef()
        ]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $productWithCategory->getDesignation());
    }
    public function testShowRouteWithCategoryAndSubCategorySlug()
    {
        $productWithCategoryAndSubCategory = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-category-and-subcategory']);

        $this->client->request('GET', $this->urlGenerator->generate('product_show_withCategoryAndSubCategory', [
            'slug' => $productWithCategoryAndSubCategory->getSlug(),
            'categorySlug' => $productWithCategoryAndSubCategory->getCategory()->getSlug(),
            'subCategorySlug' => $productWithCategoryAndSubCategory->getSubCategory()->getSlug(),
            'publicRef' => $productWithCategoryAndSubCategory->getPublicRef()
        ]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $productWithCategoryAndSubCategory->getDesignation());

    }
    public function testShowMissingRouteParams()
    {
        $productWithCategory = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-category']);

        $this->expectException(InvalidParameterException::class);
        $this->client->request('GET', $this->urlGenerator->generate('product_show_withCategory', [
            'slug' => $productWithCategory->getSlug(),
            'publicRef' => $productWithCategory->getPublicRef()
        ]));
    }
    public function testShowWithEmptyStringSlugParam()
    {
        $productWithCategoryAndSubCategory = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-category-and-subcategory']);

        $this->expectException(InvalidParameterException::class);
        $this->client->request('GET', $this->urlGenerator->generate('product_show_withCategoryAndSubCategory', [
            'slug' => '',
            'categorySlug' => $productWithCategoryAndSubCategory->getCategory()->getSlug(),
            'subCategorySlug' => $productWithCategoryAndSubCategory->getSubCategory()->getSlug(),
            'publicRef' => $productWithCategoryAndSubCategory->getPublicRef()
        ]));
    }
    public function testShowWithWrongSlugParam()
    {
        $productWithCategory = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-category']);

        $this->client->request('GET', $this->urlGenerator->generate('product_show_withCategory', [
            'slug' => $productWithCategory->getSlug(),
            'categorySlug' => 'un-slug-de-categorie-incorrect',
            'publicRef' => $productWithCategory->getPublicRef()
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    public function testShowWithInexistantPublicRefParam()
    {
        $product = $this->findEntity(ProductRepository::class, ['slug' => 'product-without-category-or-subcategory']);

        $this->client->request('GET', $this->urlGenerator->generate('product_show', [
            'slug' => $product->getSlug(),
            'publicRef' => 'inexistantpublicref'
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    public function testShowContainsCorrectBreadCrumb()
    {
        $productWithCategory = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-category']);
        $this->client->request('GET', $this->urlGenerator->generate('product_show_withCategory', [
            'slug' => $productWithCategory->getSlug(),
            'categorySlug' => $productWithCategory->getCategory()->getSlug(),
            'publicRef' => $productWithCategory->getPublicRef()
        ]));
        $this->assertSelectorTextContains('.breadcrumb-home-link', 'Accueil');
        $this->assertSelectorTextContains('.breadcrumb-link', $productWithCategory->getCategory()->getName());
        $this->assertSelectorTextContains('.breadcrumb-item', $productWithCategory->getDesignation());

        $productWithCategoryAndSubCategory = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-category-and-subcategory']);
        $this->client->request('GET', $this->urlGenerator->generate('product_show_withCategoryAndSubCategory', [
            'slug' => $productWithCategoryAndSubCategory->getSlug(),
            'categorySlug' => $productWithCategoryAndSubCategory->getCategory()->getSlug(),
            'subCategorySlug' => $productWithCategoryAndSubCategory->getSubCategory()->getSlug(),
            'publicRef' => $productWithCategoryAndSubCategory->getPublicRef()
        ]));
        $this->assertSelectorTextContains('.breadcrumb-home-link', 'Accueil');
        $this->assertSelectorTextContains('.breadcrumb-link:nth-child(2)', $productWithCategoryAndSubCategory->getCategory()->getName());
        $this->assertSelectorTextContains('.breadcrumb-link:nth-child(3)', $productWithCategoryAndSubCategory->getSubCategory()->getName());
        $this->assertSelectorTextContains('.breadcrumb-item', $productWithCategoryAndSubCategory->getDesignation());
    }
    public function testShowAddCountViewToProduct()
    {
        $productWithCategoryAndSubCategory = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-category-and-subcategory']);
        $this->client->request('GET', $this->urlGenerator->generate('product_show_withCategoryAndSubCategory', [
            'slug' => $productWithCategoryAndSubCategory->getSlug(),
            'categorySlug' => $productWithCategoryAndSubCategory->getCategory()->getSlug(),
            'subCategorySlug' => $productWithCategoryAndSubCategory->getSubCategory()->getSlug(),
            'publicRef' => $productWithCategoryAndSubCategory->getPublicRef()
        ]));
        $this->assertResponseIsSuccessful();
        /** @var Product */
        $productWithCategoryAndSubCategory = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-category-and-subcategory']);
        $this->assertEquals(1, $productWithCategoryAndSubCategory->getCountViews());
    }
    //TEST INDEX
    public function testIndexRender()
    {
        $this->client->request('GET', $this->urlGenerator->generate('product_index'));
        $this->assertResponseIsSuccessful();
    }
    public function testIndexContainsCorrectBreadCrumb()
    {
        $this->client->request('GET', $this->urlGenerator->generate('product_index'));
        $this->assertSelectorTextContains('.breadcrumb-item', 'Recherche');
        $this->assertSelectorTextContains('.breadcrumb-home-link', 'Accueil');
    }
}