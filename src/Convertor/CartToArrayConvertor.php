<?php
namespace App\Convertor;

use App\Entity\Cart;
use App\Entity\CartLine;
use App\Convertor\ConvertorTrait;
use App\Convertor\ShopConvertorTrait;

class CartToArrayConvertor
{
    use ConvertorTrait;
    use ShopConvertorTrait;

    /**
     * @param Cart[]|Cart $data
     * @return array
     */
    public function convert($data): array 
    {
        return $this->convertOneOrMore($data);
    }

    private function convertOne(Cart $cart): array 
    {
        return [
            'id' => $cart->getId(),
            'cartLines' => $this->convertCartLinesToArray($cart->getCartLines()),
            'totalPrice' => $cart->getTotalPrice(),
            'count' => $cart->getCount(),
            'updatedAt' => $cart->getUpdatedAt()->format('d/m/Y à H:i')
        ];
    }  
    
    private function convertCartLinetoArray(CartLine $cartLine): array 
    {
        $productToArrayConvertor = new ProductToArrayConvertor($this->urlGenerator, $this->picturePathResolver, $this->priceFormater, $this->productShowUrlResolver);
        return [
            'id' => $cartLine->getId(),
            'product' => $productToArrayConvertor->convert($cartLine->getProduct()),
            'quantity' => $cartLine->getQuantity(),
            'totalPrice' => $cartLine->getTotalPrice()
        ];
    }
    

    /**
     * @param CartLine[] $cartLines
     * @return array
     */
    private function convertCartLinesToArray($cartLines): array 
    {
        $cartLinesToArray = [];
        foreach($cartLines as $cartLine)
        {
            $cartLinesToArray[] = $this->convertCartLineToArray($cartLine);
        }
        return $cartLinesToArray;
    }
}