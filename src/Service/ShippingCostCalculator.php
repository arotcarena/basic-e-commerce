<?php
namespace App\Service;

use App\Entity\Cart;

class ShippingCostCalculator
{
    public function calculate(Cart $cart): int
    {
        return 550; // 5.50€ : A MODIFIER
    }
}