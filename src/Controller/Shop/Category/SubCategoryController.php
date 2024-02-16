<?php
namespace App\Controller\Shop\Category;

use App\Repository\SubCategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class SubCategoryController extends AbstractController
{
    public function __construct(
        private SubCategoryRepository $subCategoryRepository
    )
    {

    }

    #[Route(
        '/{categorySlug}/{subCategorySlug}.html', 
        name: 'subCategory_show', 
        requirements: [
            'categorySlug' => '[a-z0-9-]+', 
            'subCategorySlug' => '[a-z0-9-]+'
            ]
    )]
    public function index(string $categorySlug, string $subCategorySlug): Response
    {
        $subCategory = $this->subCategoryRepository->findByBothSlugs($categorySlug, $subCategorySlug);
        if($subCategory === null)
        {
            throw new NotFoundResourceException('La page que vous recherchez n\'existe pas');
        }

        return $this->render('shop/category/subCategory/show.html.twig', [
            'subCategory' => $subCategory,
            'category' => $subCategory->getParentCategory()
        ]);
    }
}