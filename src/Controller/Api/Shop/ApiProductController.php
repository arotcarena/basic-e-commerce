<?php
namespace App\Controller\Api\Shop;

use App\Convertor\ProductToArrayConvertor;
use App\Form\DataModel\SearchParams;
use App\Repository\ProductRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class ApiProductController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private ProductToArrayConvertor $productToArrayConvertor
    )
    {

    }

    #[Route('/api/product/search', name: 'api_product_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $q = $request->query->get('q');
        if($q === '')
        {
            return new JsonResponse(['products' => [], 'count' => 0]);
        }
        $limit = $request->query->get('limit', 4);
        $products = $this->productRepository->qSearch($q, $limit);
        $count = $this->productRepository->countQSearch($q);
        return new JsonResponse([
            'products' => $this->productToArrayConvertor->convert($products),
            'count' => $count
        ]);
    }

    #[Route('/api/product/index', name: 'api_product_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse 
    {
        $minPrice = !empty($request->query->get('minPrice')) ? (int)$request->query->get('minPrice'): null;
        $maxPrice = !empty($request->query->get('maxPrice')) ? (int)$request->query->get('maxPrice'): null;

        $searchParams = (new SearchParams)
                        ->setMinPrice($minPrice)
                        ->setMaxPrice($maxPrice)
                        ->setQ($request->query->get('q'))
                        ->setSort($request->query->get('sort'))
                        ->setCategoryId((int)$request->query->get('categoryId'))
                        ->setSubCategoryId((int)$request->query->get('subCategoryId'))
                        ;
        $products = $this->productRepository->filter($searchParams);
        $count = $this->productRepository->countFilter($searchParams);
        return new JsonResponse([
            'products' => $this->productToArrayConvertor->convert($products),
            'count' => $count
        ]);
    }

}