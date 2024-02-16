<?php
namespace App\Service;

use App\Entity\Product;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductShowUrlResolver
{
    public function __construct(
        private UrlGeneratorInterface $urlGeneratorInterface
    )
    {

    }

    public function getUrl(Product $product): string
    {
        if($product->getCategory() && $product->getSubCategory())
        {
            return $this->urlGeneratorInterface->generate('product_show_withCategoryAndSubCategory', [
                'categorySlug' => $product->getCategory()->getSlug(),
                'subCategorySlug' => $product->getSubCategory()->getSlug(),
                'slug' => $product->getSlug(),
                'publicRef' => $product->getPublicRef(),
            ]);
        }
        elseif($product->getCategory())
        {
            return $this->urlGeneratorInterface->generate('product_show_withCategory', [
                'categorySlug' => $product->getCategory()->getSlug(),
                'slug' => $product->getSlug(),
                'publicRef' => $product->getPublicRef(),
            ]);
        }
        return $this->urlGeneratorInterface->generate('product_show', [
            'slug' => $product->getSlug(),
            'publicRef' => $product->getPublicRef()
        ]);
    }
}