<?php
namespace App\Service;

use App\Entity\SubCategory;
use App\Repository\SubCategoryRepository;

class UniqueListPositionVerificator
{
    public function __construct(
        private SubCategoryRepository $subCategoryRepository
    )
    {

    }

    public function verifySubCategory(SubCategory $subCategory)
    {
        $existingSubCategory = $this->subCategoryRepository->findOneBy([
            'parentCategory' => $subCategory->getParentCategory(),
            'listPosition' => $subCategory->getListPosition()
        ]);
        if(!$existingSubCategory)
        {
            return true;
        }
        // en cas d'update d'une subcategory en conservant la même listPosition
        if($existingSubCategory->getId() === $subCategory->getId() && $subCategory->getId() !== null)
        {
            return true;
        }
        return false;
    }
}