<?php
namespace App\Tests\UnitAndIntegration\Service;

use App\Entity\Picture;
use App\Entity\Product;
use App\Service\ProductHasFirstPictureVerificator;
use PHPUnit\Framework\TestCase;

/**
 * @group Service
 */
class ProductHasFirstPictureVerificatorTest extends TestCase
{
    private ProductHasFirstPictureVerificator $verificator;

    public function setUp(): void 
    {
        $this->verificator = new ProductHasFirstPictureVerificator;
    }
    public function testVerifyProductWithoutFirstPicture()
    {
        $product = (new Product)
                    ->addPicture(
                        (new Picture)
                        ->setListPosition(2)
                    )
                    ->addPicture(
                        (new Picture)
                        ->setListPosition(4)
                    );
        $this->assertFalse($this->verificator->verify($product));
    }   
    public function testVerifyProductWithFirstPicture()
    {
        $product = (new Product)
                    ->addPicture(
                        (new Picture)
                        ->setListPosition(2)
                    )
                    ->addPicture(
                        (new Picture)
                        ->setListPosition(4)
                    )
                    ->addPicture(
                        (new Picture)
                        ->setListPosition(1)
                    );
        $this->assertTrue($this->verificator->verify($product));
    }
}