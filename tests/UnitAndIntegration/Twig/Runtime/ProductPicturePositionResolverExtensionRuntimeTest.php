<?php
namespace App\Tests\UnitAndIntegration\Twig\Runtime;

use App\Entity\Picture;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;
use App\Twig\Runtime\ProductPicturePositionResolverExtensionRuntime;

/**
 * @group Twig
 */
class ProductPicturePositionResolverExtensionRuntimeTest extends TestCase
{
    private ProductPicturePositionResolverExtensionRuntime $resolver;

    public function setUp(): void 
    {
        $this->resolver = new ProductPicturePositionResolverExtensionRuntime;
    }

    public function testGetPictureAtPositionWithProductThatHaveNoPictures()
    {
        $product = new Product;

        $this->assertNull($this->resolver->getPictureAtPosition($product, 1));
    }
    public function testGetPictureAtPositionWithInexistantPictureAtThisPosition()
    {
        $product = (new Product)
                    ->addPicture(
                        (new Picture)
                        ->setAlt('picture 4')
                        ->setListPosition(4)
                    )
                    ->addPicture(
                        (new Picture)
                        ->setAlt('picture 1')
                        ->setListPosition(1)
                    )
                    ->addPicture(
                        (new Picture)
                        ->setAlt('picture 2')
                        ->setListPosition(2)
                    );
        $this->assertNull($this->resolver->getPictureAtPosition($product, 3));
    }
    public function testGetPictureAtPositionWithValidPictureAtThisPosition()
    {
        $product = (new Product)
                    ->addPicture(
                        (new Picture)
                        ->setAlt('picture 4')
                        ->setListPosition(4)
                    )
                    ->addPicture(
                        (new Picture)
                        ->setAlt('picture 1')
                        ->setListPosition(1)
                    )
                    ->addPicture(
                        (new Picture)
                        ->setAlt('picture 2')
                        ->setListPosition(2)
                    );
        $this->assertEquals(
            'picture 2',
            $this->resolver->getPictureAtPosition($product, 2)->getAlt()
        );
    }
}