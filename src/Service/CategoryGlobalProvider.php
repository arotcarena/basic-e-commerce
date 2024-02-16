<?php
namespace App\Service;

use App\Convertor\CategoryToArrayConvertor;
use App\Repository\CategoryRepository;

class CategoryGlobalProvider
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private CategoryToArrayConvertor $categoryConvertor
    )
    {

    }
    public function getJsonMenuList(): string
    {
        $categories = $this->categoryRepository->findAllOrderedForMenuList();

        $categoriesToArray = $this->categoryConvertor->convert($categories);

        return json_encode($categoriesToArray);
    }
}