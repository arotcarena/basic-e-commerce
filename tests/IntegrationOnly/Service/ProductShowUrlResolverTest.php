<?php
namespace App\Tests\IntegrationOnly\Service;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\SubCategory;
use App\Service\ProductShowUrlResolver;
use Liip\ImagineBundle\Exception\Config\Filter\NotFoundException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @group Service
 */
class ProductShowUrlResolverTest extends KernelTestCase
{
    private UrlGeneratorInterface $urlGenerator;

    private ProductShowUrlResolver $productShowUrlResolver;


    public function setUp(): void
    {
        parent::setUp();

        $this->urlGenerator = static::getContainer()->get(UrlGeneratorInterface::class);

        $this->productShowUrlResolver = static::getContainer()->get(ProductShowUrlResolver::class); 
    }

    public function testInvalidProductParam()
    {
        $product = new Product;
        $this->expectException(InvalidParameterException::class);
        $this->productShowUrlResolver->getUrl($product);

        $product = $this->createValidProduct()
                        ->setCategory(new Category)
                        ;
        $this->expectException(InvalidParameterException::class);
        $this->productShowUrlResolver->getUrl($product);
    }

    public function testProductWithCategoryAndSubCategory()
    {
        $product = $this->createValidProduct()
                        ->setCategory(
                            (new Category)
                            ->setSlug('slug-de-category')
                        )
                        ->setSubCategory(
                            (new SubCategory)
                            ->setSlug('slug-de-subcategory')
                        )
                        ;
        
        $this->assertEquals(
            $this->urlGenerator->generate('product_show_withCategoryAndSubCategory', [
                'publicRef' => $product->getPublicRef(),
                'slug' => $product->getSlug(),
                'categorySlug' => $product->getCategory()->getSlug(),
                'subCategorySlug' => $product->getSubCategory()->getSlug()
            ]),
            $this->productShowUrlResolver->getUrl($product)
        );
    }

    public function testProductWithCategory()
    {
        $product = $this->createValidProduct()
                        ->setCategory(
                            (new Category)
                            ->setSlug('slug-de-category')
                        )
                        ;
        
        $this->assertEquals(
            $this->urlGenerator->generate('product_show_withCategory', [
                'publicRef' => $product->getPublicRef(),
                'slug' => $product->getSlug(),
                'categorySlug' => $product->getCategory()->getSlug()
            ]),
            $this->productShowUrlResolver->getUrl($product)
        );
    }

    public function testProductWithoutCategory()
    {
        $product = $this->createValidProduct();
        
        $this->assertEquals(
            $this->urlGenerator->generate('product_show', [
                'publicRef' => $product->getPublicRef(),
                'slug' => $product->getSlug()
            ]),
            $this->productShowUrlResolver->getUrl($product)
        );
    }


    private function createValidProduct(): Product
    {
        return (new Product)
                ->setPublicRef('123456af')
                ->setSlug('slug')
                ;
    }
}