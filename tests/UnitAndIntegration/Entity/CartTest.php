<?php
namespace App\Tests\UnitAndIntegration\Entity;

use App\Entity\Cart;
use App\Entity\User;
use DateTimeImmutable;
use App\Entity\CartLine;
use App\Tests\UnitAndIntegration\Entity\EntityTest;

/**
 * @group Entity
 */
class CartTest extends EntityTest
{
    public function testValidCart()
    {
        $this->assertHasErrors(0, $this->createValidCart());
    }

    public function testInvalidNoUser()
    {
        $cart = (new Cart)
                ->addCartLine(new CartLine)
                ->setTotalPrice(2000)
                ->setUpdatedAt(new DateTimeImmutable())
                ;
        $this->assertHasErrors(1, $cart);
    }
    public function testInvalidNoCartLines()
    {
        $cart = (new Cart)
                ->setUser(new User)
                ->setTotalPrice(2000)
                ->setUpdatedAt(new DateTimeImmutable())
                ;
        $this->assertHasErrors(1, $cart);
    }
    public function testInvalidNegativeOrZeroTotalPrice()
    {
        $this->assertHasErrors(
            1, 
            $this->createValidCart()->setTotalPrice(-4)
        );
        $this->assertHasErrors(
            1, 
            $this->createValidCart()->setTotalPrice(0)
        );
    }


    private function createValidCart(): Cart
    {
        return (new Cart)
                ->setUser(new User)
                ->addCartLine(new CartLine)
                ->setTotalPrice(2000)
                ->setUpdatedAt(new DateTimeImmutable())
                ;
    }
}