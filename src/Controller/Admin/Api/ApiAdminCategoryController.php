<?php
namespace App\Controller\Admin\Api;

use App\Entity\SubCategory;
use App\Repository\CategoryRepository;
use App\Convertor\CategoryToArrayConvertor;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[IsGranted('ROLE_ADMIN')]
class ApiAdminCategoryController extends AbstractController
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private CategoryToArrayConvertor $categoryToArrayConvertor
    )
    {

    }

    #[Route('/admin/api/category/{id}/subCategory_ids', name: 'admin_api_category_findSubCategoryIdsByCategoryId')]
    public function findSubCategoryIdsByCategoryId(int $id): JsonResponse
    {
        $category = $this->categoryRepository->findOneOrdered($id);
        if(!$category) 
        {
            return new JsonResponse([
                'errors' => ['La catégorie avec l\'id "'.$id.'" n\'existe pas !']
            ], 500);
        }
        $ids = [];
        /** @var SubCategory $subCategory */
        foreach($category->getSubCategories() as $subCategory)
        {
            $ids[] = $subCategory->getId();
        }
        return new JsonResponse($ids);
    }

    #[Route('/admin/api/category/{id}', name: 'admin_api_category_findCategory')]
    public function findCategory(int $id): JsonResponse
    {
        $category = $this->categoryRepository->findOneOrdered($id);
        if(!$category) 
        {
            return new JsonResponse([
                'errors' => ['La catégorie avec l\'id "'.$id.'" n\'existe pas !']
            ], 500);
        }
        return new JsonResponse(
            $this->categoryToArrayConvertor->convert($category)
        );
    }
}