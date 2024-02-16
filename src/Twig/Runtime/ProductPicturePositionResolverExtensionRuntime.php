<?php

namespace App\Twig\Runtime;

use App\Entity\Picture;
use App\Entity\Product;
use Twig\Extension\RuntimeExtensionInterface;

class ProductPicturePositionResolverExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct()
    {
        // Inject dependencies if needed
    }

    public function getPictureAtPosition(Product $product, int $position): ?Picture
    {
        foreach($product->getPictures() as $picture)
        {
            if($picture->getListPosition() === $position)
            {
                return $picture;
            }
        }
        return null;
    }
}
