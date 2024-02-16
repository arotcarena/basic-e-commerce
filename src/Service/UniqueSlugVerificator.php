<?php
namespace App\Service;

use App\Entity\Product;
use App\Entity\SubCategory;
use App\Repository\ProductRepository;
use App\Repository\SubCategoryRepository;
use Exception;

class UniqueSlugVerificator
{
    public function __construct(
        private ProductRepository $productRepository,
        private SubCategoryRepository $subCategoryRepository
    )
    {
        
    }

    /**
     * @param Product|SubCategory $subject
     *
     * @return void
     */
    public function verify($subject): bool
    {
        if($subject instanceof Product)
        {
            return $this->verifyProduct($subject);
        }
        if($subject instanceof SubCategory)
        {
            return $this->verifySubCategory($subject);
        }
    }

    private function verifyProduct(Product $product): bool 
    {
        $productWithSameSlug = $this->productRepository->findOneBy([
            'category' => $product->getCategory(),
            'subCategory' => $product->getSubCategory(),
            'slug' => $product->getSlug()
        ]);
        if(!$productWithSameSlug)
        {
            return true;
        }
        // en cas d'update d'un product en conservant le même slug
        if($productWithSameSlug->getId() === $product->getId() && $product->getId() !== null)
        {
            return true;
        }
        return false;
    }

    private function verifySubCategory(SubCategory $subCategory): bool 
    {
        $subCategoryWithSameSlug = $this->subCategoryRepository->findOneBy([
            'parentCategory' => $subCategory->getParentCategory(),
            'slug' => $subCategory->getSlug()
        ]);
        if(!$subCategoryWithSameSlug)
        {
            return true;
        }
        // en cas d'update d'une subCategory en conservant le même slug
        if($subCategoryWithSameSlug->getId() === $subCategory->getId()  && $subCategory->getId() !== null)
        {
            return true;
        }
        return false;
    }
}