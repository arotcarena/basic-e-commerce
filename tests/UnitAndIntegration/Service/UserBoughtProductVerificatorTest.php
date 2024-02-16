<?php
namespace App\Tests\UnitAndIntegration\Service;

use App\Convertor\PurchaseLineProductConvertor;
use App\Entity\User;
use App\Entity\Product;
use App\Entity\Purchase;
use App\Entity\PurchaseLine;
use App\Service\UserBoughtProductVerificator;
use PHPUnit\Framework\TestCase;


/**
 * @group Service
 */
class UserBoughtProductVerificatorTest extends TestCase
{
    private UserBoughtProductVerificator $verificator;

    private PurchaseLineProductConvertor $purchaseLineProductConvertor;

    public function setUp(): void
    {
        $this->purchaseLineProductConvertor = new PurchaseLineProductConvertor;
        $this->verificator = new UserBoughtProductVerificator($this->purchaseLineProductConvertor);
    }

    public function testWithUserThatDidNotBuyAnyProduct()
    {
        $this->assertFalse($this->verificator->verify(new User, new Product));
    }
    public function testWithUserThatDidNotBuyProduct()
    {   
        $product = (new Product)->setDesignation('produit');
        $user = new User;
        $user->addPurchase(
            (new Purchase)
            ->setUser($user)
            ->addPurchaseLine(
                (new PurchaseLine)
                ->setProduct([])
                ->setQuantity(3)
            )
        );
        $this->assertFalse($this->verificator->verify($user, $product));
    }
    public function testWithUserThatBoughtProduct()
    {
        $product = (new Product)->setDesignation('produit');
        $user = new User;
        $user->addPurchase(
            (new Purchase)
            ->setUser($user)
            ->addPurchaseLine(
                (new PurchaseLine)
                ->setProduct([])
                ->setQuantity(4)
            )
            ->addPurchaseLine(
                (new PurchaseLine)
                ->setProduct($this->purchaseLineProductConvertor->convert($product))
                ->setQuantity(1)
            )
        );
        $this->assertTrue($this->verificator->verify($user, $product));
    }
}