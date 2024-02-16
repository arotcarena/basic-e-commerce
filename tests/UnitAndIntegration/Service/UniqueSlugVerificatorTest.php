<?php
namespace App\Tests\UnitAndIntegration\Service;

use TypeError;
use App\Entity\Cart;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\SubCategory;
use PHPUnit\Framework\TestCase;
use App\Repository\ProductRepository;
use App\Service\UniqueSlugVerificator;
use App\Repository\SubCategoryRepository;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @group Service
 */
class UniqueSlugVerificatorTest extends TestCase
{
    private MockObject $productRepository;

    private MockObject $subCategoryRepository;

    private UniqueSlugVerificator $verificator;

    public function setUp(): void 
    {
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->subCategoryRepository = $this->createMock(SubCategoryRepository::class);

        $this->verificator = new UniqueSlugVerificator($this->productRepository, $this->subCategoryRepository);
    }
    public function testWrongClassSubjectParameter()
    {
        $this->expectException(TypeError::class);
        $this->verificator->verify(new Category);
    }
    public function testExistingProductSlug()
    {
        $product = (new Product)
                    ->setCategory(new Category)
                    ->setSubCategory(new SubCategory)
                    ->setSlug('slug-existant')
                    ;
        $this->productRepository->expects($this->once())
                                ->method('findOneBy')
                                ->with(['category' => $product->getCategory(), 'subCategory' => $product->getSubCategory(), 'slug' => $product->getSlug()])
                                ->willReturn(new Product)
                                ;
        $this->assertFalse($this->verificator->verify($product));
    }
    public function testExistingSubCategorySlug()
    {
        $subCategory = (new SubCategory)
                    ->setParentCategory(new Category)
                    ->setSlug('slug-existant')
                    ;
        $this->subCategoryRepository->expects($this->once())
                                ->method('findOneBy')
                                ->with(['parentCategory' => $subCategory->getParentCategory(), 'slug' => $subCategory->getSlug()])
                                ->willReturn(new Product)
                                ;
        $this->assertFalse($this->verificator->verify($subCategory));
    }
    public function testValidProductSlug()
    {
        $product = (new Product)
                    ->setCategory(new Category)
                    ->setSubCategory(new SubCategory)
                    ->setSlug('slug-valide')
                    ;
        $this->productRepository->expects($this->once())
                                ->method('findOneBy')
                                ->with(['category' => $product->getCategory(), 'subCategory' => $product->getSubCategory(), 'slug' => $product->getSlug()])
                                ->willReturn(null)
                                ;
        $this->assertTrue($this->verificator->verify($product));
    }
    public function testValidSubCategorySlug()
    {
        $subCategory = (new SubCategory)
                    ->setParentCategory(new Category)
                    ->setSlug('slug-valide')
                    ;
        $this->subCategoryRepository->expects($this->once())
                                ->method('findOneBy')
                                ->with(['parentCategory' => $subCategory->getParentCategory(), 'slug' => $subCategory->getSlug()])
                                ->willReturn(null)
                                ;
        $this->assertTrue($this->verificator->verify($subCategory));
    }
}