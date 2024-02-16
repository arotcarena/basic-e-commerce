<?php
namespace App\Convertor;

use App\Entity\Category;
use App\Entity\SubCategory;
use App\Convertor\ConvertorTrait;
use App\Convertor\ShopConvertorTrait;

class CategoryToArrayConvertor
{
    use ConvertorTrait;
    use ShopConvertorTrait;

    /**
     * @param Category[]|Category $data
     * @return array
     */
    public function convert($data): array 
    {
        return $this->convertOneOrMore($data);
    }

    private function convertOne(Category $category): array 
    {
        return [
            'id' => $category->getId(),
            'name' => str_replace(' ', '_', $category->getName()),
            'target' => $this->urlGenerator->generate('category_show', [
                'slug' => $category->getSlug()
            ]),
            'listPosition' => $category->getListPosition(),
            'subCategories' => $this->convertSubCategoriesToArray($category->getSubCategories())
        ];
    }  
    
    private function convertSubCategoryToArray(SubCategory $subCategory): array 
    {
        return [
            'id' => $subCategory->getId(),
            'name' => str_replace(' ', '_', $subCategory->getName()),
            'target' => $this->urlGenerator->generate('subCategory_show', [
                'categorySlug' => $subCategory->getParentCategory()->getSlug(),
                'subCategorySlug' => $subCategory->getSlug()
            ]),
            'picture' => [
                'path' => $this->picturePathResolver->getPath($subCategory->getPicture()),
                'alt' => str_replace(' ', '_', $subCategory->getPicture() !== null ? $subCategory->getPicture()->getAlt(): 'texte alternatif par défaut') 
            ],
            'listPosition' => $subCategory->getListPosition()
        ];
    }
    

    /**
     * @param SubCategory[] $subCategories
     * @return array
     */
    private function convertSubCategoriesToArray($subCategories): array 
    {
        $subCategoriesToArray = [];
        foreach($subCategories as $subCategory)
        {
            $subCategoriesToArray[] = $this->convertSubCategoryToArray($subCategory);
        }
        return $subCategoriesToArray;
    }
}