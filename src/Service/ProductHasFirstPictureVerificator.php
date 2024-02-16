<?php
namespace App\Service;

use App\Entity\Product;

class ProductHasFirstPictureVerificator
{
    public function verify(Product $product)
    {
        $hasFirstPicture = false;
        foreach($product->getPictures() as $picture)
        {
            if($picture->getListPosition() === 1)
            {
                $hasFirstPicture = true;
            }
        }
        return $hasFirstPicture;
    }
}