<?php
namespace App\Service;

use App\Convertor\PurchaseLineProductConvertor;
use App\Entity\Product;
use App\Entity\User;

class UserBoughtProductVerificator
{
    public function __construct(
        private PurchaseLineProductConvertor $purchaseLineProductConvertor
    )
    {

    }

    public function verify(User $user, Product $product): bool
    {
        foreach($user->getPurchases() as $purchase)
        {
            foreach($purchase->getPurchaseLines() as $purchaseLine)
            {
                if($purchaseLine->getProduct() === $this->purchaseLineProductConvertor->convert($product))
                {
                    return true;
                }   
            }
        }
        return false;
    }
}