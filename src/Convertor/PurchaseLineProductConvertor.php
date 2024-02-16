<?php
namespace App\Convertor;

use App\Entity\Product;

class PurchaseLineProductConvertor
{
    public function convert(Product $product)
    {
        return [
            'id' => $product->getId(),
            'publicRef' => $product->getPublicRef(),
            'privateRef' => $product->getPrivateRef(),
            'designation' => $product->getDesignation(),
            'price' => $product->getPrice()
        ];
    }
}