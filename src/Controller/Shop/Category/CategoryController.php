<?php
namespace App\Controller\Shop\Category;

use App\Repository\CategoryRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class CategoryController extends AbstractController
{
    public function __construct(
        private CategoryRepository $categoryRepository
    )
    {

    }

    #[Route('/{slug}.html', name: 'category_show', requirements: ['slug' => '[a-z0-9-]+'])]
    public function index(string $slug): Response
    {
        $category = $this->categoryRepository->findOneBySlug($slug);
        if($category === null)
        {
            throw new NotFoundResourceException('La page que vous recherchez n\'existe pas');
        }

        return $this->render('shop/category/show.html.twig', [
            'category' => $category
        ]);
    }
}