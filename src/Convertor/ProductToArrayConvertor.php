<?php
namespace App\Convertor;

use App\Entity\Product;
use App\Convertor\ConvertorTrait;
use App\Convertor\ShopConvertorTrait;
use App\Service\ProductShowUrlResolver;

class ProductToArrayConvertor
{
    use ConvertorTrait;
    use ShopConvertorTrait;



    /**
     * @param Product|Product[] $data
     * @param bool $light
     * @return array
     */
    public function convert($data): array
    {
        return $this->convertOneOrMore($data);
    }
    
    public function convertOne(Product $product): array 
    {
        $fullName = $product->getDesignation() .   
                    ($product->getCategory() ? ' dans ' . $product->getCategory()->getName(): '') .
                    ($product->getSubCategory() ? ' > ' . $product->getSubCategory()->getName(): '');

        return [
            'id' => $product->getId(),
            'designation' => $product->getDesignation(),
            'fullName' => $fullName,
            'categoryName' => $product->getCategory() ? $product->getCategory()->getName(): null,
            'subCategoryName' => $product->getSubCategory() ? $product->getSubCategory()->getName(): null,
            'price' => $product->getPrice(),
            'formatedPrice' => $this->priceFormater->format($product->getPrice()),
            'target' => $this->productShowUrlResolver->getUrl($product),
            'firstPicture' => [
                'path' => $this->picturePathResolver->getPath($product->getFirstPicture(), 'index'),
                'alt' => $this->picturePathResolver->getAlt($product->getFirstPicture())
            ],
            'stock' => $product->getStock()
        ];
    }
}