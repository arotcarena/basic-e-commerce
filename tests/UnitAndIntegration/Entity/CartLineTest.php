<?php
namespace App\Tests\UnitAndIntegration\Entity;

use App\Entity\Cart;
use App\Entity\Product;
use App\Entity\CartLine;
use App\Tests\UnitAndIntegration\Entity\EntityTest;

/**
 * @group Entity
 */
class CartLineTest extends EntityTest
{
    public function testValidCategory()
    {
        $this->assertHasErrors(0, $this->createValidCartLine());
    }
    public function testInvalidNoCart()
    {
        $this->assertHasErrors(
            1,
            $this->createValidCartLine()->setCart(null)
        );
    }
    public function testInvalidNoProduct()
    {
        $this->assertHasErrors(
            1,
            $this->createValidCartLine()->setProduct(null)
        );
    }
    public function testInvalidNegativeOrZeroQuantity()
    {
        $this->assertHasErrors(
            1, 
            $this->createValidCartLine()->setQuantity(-4)
        );
        $this->assertHasErrors(
            1, 
            $this->createValidCartLine()->setQuantity(0)
        );
    }
    public function testInvalidNullQuantity()
    {
        $cartLine = (new CartLine)
                    ->setCart(new Cart)
                    ->setProduct(new Product)
                    ->setTotalPrice(2000)
                    ;
        $this->assertHasErrors(1, $cartLine);
    }
    public function testInvalidNegativeOrZeroTotalPrice()
    {
        $this->assertHasErrors(
            1, 
            $this->createValidCartLine()->setTotalPrice(-4)
        );
        $this->assertHasErrors(
            1, 
            $this->createValidCartLine()->setTotalPrice(0)
        );
    }
    public function testInvalidNullTotalPrice()
    {
        $cartLine = (new CartLine)
                    ->setCart(new Cart)
                    ->setProduct(new Product)
                    ->setQuantity(1)
                    ;
        $this->assertHasErrors(1, $cartLine);
    }

    private function createValidCartLine(): CartLine
    {
        return (new CartLine)
                ->setCart(new Cart)
                ->setProduct(new Product)
                ->setQuantity(1)
                ->setTotalPrice(2000)
                ;
    }
}