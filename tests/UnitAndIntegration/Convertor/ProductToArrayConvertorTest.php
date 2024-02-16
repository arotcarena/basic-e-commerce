<?php
namespace App\Tests\UnitAndIntegration\Convertor;

use App\Tests\Utils\FixturesTrait;
use App\Service\PicturePathResolver;
use App\Repository\ProductRepository;
use App\Convertor\ProductToArrayConvertor;
use App\DataFixtures\Tests\ProductTestFixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @group Convertor
 */
class ProductToArrayConvertorTest extends KernelTestCase
{
    use FixturesTrait;

    private ProductRepository $productRepository;

    private UrlGeneratorInterface $urlGenerator;

    private PicturePathResolver $picturePathResolver;

    private ProductToArrayConvertor $productConvertor;


    public function setUp(): void
    {
        $this->productRepository = static::getContainer()->get(ProductRepository::class);
        $this->urlGenerator = static::getContainer()->get(UrlGeneratorInterface::class);
        $this->picturePathResolver = static::getContainer()->get(PicturePathResolver::class);
        $this->productConvertor = static::getContainer()->get(ProductToArrayConvertor::class);

        $this->loadFixtures([ProductTestFixtures::class]);
    }

    public function testContainsCorrectKeysWhenConvertOne()
    {
        $product = $this->productRepository->findOneBy([]);
        $returnProduct = $this->productConvertor->convert($product);

        $this->assertEquals(
            ['id', 'designation', 'fullName', 'categoryName', 'subCategoryName', 'price', 'formatedPrice', 'target', 'firstPicture', 'stock'], 
            array_keys($returnProduct)
        );
    }

    public function testContainsCorrectKeysWhenConvertAll()
    {
        $products = $this->productRepository->findAll();
        $returnProduct = $this->productConvertor->convert($products)[0];

        $this->assertEquals(
            ['id', 'designation', 'fullName', 'categoryName', 'subCategoryName', 'price', 'formatedPrice', 'target', 'firstPicture', 'stock'],
            array_keys($returnProduct)
        );

        $this->assertEquals(
            ['path', 'alt'], 
            array_keys($returnProduct['firstPicture'])
        );
    }
  
    public function testReturnCorrectProductsCount()
    {
        $products = $this->productRepository->findAll();
        $data = $this->productConvertor->convert($products);

        $this->assertCount(
            count($products), 
            $data   
        );
    }
    public function testContainsCorrectProductDesignation()
    {
        $product = $this->productRepository->findOneBy([]);
        $returnProduct = $this->productConvertor->convert($product);
        $this->assertEquals(
            $product->getDesignation(), 
            $returnProduct['designation']
        );
    }
    public function testCorrectUrlIsSetWhenProductHaveCategoryAndSubCategory()
    {
        $product = $this->productRepository->findOneBy([]);
        $url = $this->urlGenerator->generate('product_show_withCategoryAndSubCategory', [
            'slug' => $product->getSlug(),
            'publicRef' => $product->getPublicRef(),
            'categorySlug' => $product->getCategory()->getSlug(),
            'subCategorySlug' => $product->getSubCategory()->getSlug()
        ]);

        $returnProduct = $this->productConvertor->convert($product);

        $this->assertEquals(
            $url, 
            $returnProduct['target']
        );
    }
    public function testCorrectUrlIsSetWhenProductHaveCategoryButNoSubCategory()
    {
        $product = $this->productRepository->findOneBy([]);
        $product->setSubCategory(null);

        $url = $this->urlGenerator->generate('product_show_withCategory', [
            'slug' => $product->getSlug(),
            'publicRef' => $product->getPublicRef(),
            'categorySlug' => $product->getCategory()->getSlug()
        ]);
        
        $returnProduct = $this->productConvertor->convert($product);

        $this->assertEquals(
            $url, 
            $returnProduct['target']
        );
    }
    public function testCorrectUrlIsSetWhenProductHaveNoCategoryAndSubCategory()
    {
        $product = $this->productRepository->findOneBy([]);
        $product->setSubCategory(null)
                ->setCategory(null);
        
        $url = $this->urlGenerator->generate('product_show', [
            'slug' => $product->getSlug(),
            'publicRef' => $product->getPublicRef()
        ]);
        
        $returnProduct = $this->productConvertor->convert($product);

        $this->assertEquals(
            $url, 
            $returnProduct['target']
        );
    }
} 
