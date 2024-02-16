<?php
namespace App\Tests\IntegrationOnly\Service;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\SubCategory;
use App\Tests\Utils\FixturesTrait;
use App\Repository\ProductRepository;
use App\DataFixtures\Tests\ProductWithOrWithoutCategoryTestFixtures;
use App\Repository\SubCategoryRepository;
use App\Service\UniqueSlugVerificator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group Integration
 */
class UniqueSlugVerificatorTest extends KernelTestCase
{
    use FixturesTrait;

    private UniqueSlugVerificator $verificator;

    public function setUp(): void 
    {
        parent::setUp();

        self::bootKernel();

        $this->loadFixtures([ProductWithOrWithoutCategoryTestFixtures::class]);
        $productRepository = static::getContainer()->get(ProductRepository::class);
        $subCategoryRepository = static::getContainer()->get(SubCategoryRepository::class);
        $this->verificator = new UniqueSlugVerificator($productRepository, $subCategoryRepository);
    }

    public function testExistingProductSlugWithSameCategoryAndSubCategory()
    {
        /** @var Product */
        $existingProduct = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-category-and-subcategory']);

        $product = (new Product)
                    ->setCategory($existingProduct->getCategory())
                    ->setSubCategory($existingProduct->getSubCategory())
                    ->setSlug($existingProduct->getSlug())
                    ;

        $this->assertFalse($this->verificator->verify($product));
    }
    public function testExistingProductSlugWithDifferentCategoryOrSubCategory()
    {
        /** @var Product */
        $existingProduct = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-category-and-subcategory']);

        $product = (new Product)
                    ->setCategory($existingProduct->getCategory())
                    ->setSubCategory(new SubCategory)   // different SubCategory
                    ->setSlug($existingProduct->getSlug())
                    ;

        $this->assertTrue($this->verificator->verify($product));
    }
    public function testUpdateProductKeepingSameSlug()
    {
        /** @var Product */
        $existingProduct = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-category-and-subcategory']);
        $this->assertTrue($this->verificator->verify($existingProduct));
    }
    public function testUpdateSubCategoryKeepingSameSlug()
    {
        /** @var Product */
        $product = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-category-and-subcategory']);
        $existingSubCategory = $product->getSubCategory();
        $this->assertTrue($this->verificator->verify($existingSubCategory));
    }
    public function testExistingSubCategorySlugWithSameParentCategory()
    {
        /** @var Product */
        $product = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-category-and-subcategory']);
        $existingSubCategory = $product->getSubCategory();

        $subCategory = (new SubCategory)
                    ->setParentCategory($existingSubCategory->getParentCategory())
                    ->setSlug($existingSubCategory->getSlug())
                    ;

        $this->assertFalse($this->verificator->verify($subCategory));
    }
    public function testExistingSubCategorySlugWithDifferentParentCategory()
    {
        /** @var Product */
        $product = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-category-and-subcategory']);
        $existingSubCategory = $product->getSubCategory();

        $subCategory = (new SubCategory)
                    ->setParentCategory(new Category) // different Category
                    ->setSlug($existingSubCategory->getSlug())
                    ;
                    
        $this->assertTrue($this->verificator->verify($subCategory));
    }
    public function testOriginalProductSlug()
    {
        /** @var Product */
        $existingProduct = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-category-and-subcategory']);

        $product = (new Product)
                    ->setCategory($existingProduct->getCategory())
                    ->setSubCategory($existingProduct->getSubCategory()) 
                    ->setSlug('slug-original')
                    ;

        $this->assertTrue($this->verificator->verify($product));
    }
    public function testOriginalSubCategorySlug()
    {
        /** @var Product */
        $product = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-category-and-subcategory']);
        $existingSubCategory = $product->getSubCategory();

        $subCategory = (new SubCategory)
                    ->setParentCategory($existingSubCategory->getParentCategory())
                    ->setSlug('slug-original')
                    ;
                    
        $this->assertTrue($this->verificator->verify($subCategory));
    }


    
}