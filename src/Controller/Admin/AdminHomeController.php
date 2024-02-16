<?php
namespace App\Controller\Admin;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\PurchaseRepository;
use App\Repository\ReviewRepository;
use App\Repository\SubCategoryRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



#[IsGranted('ROLE_ADMIN')]
class AdminHomeController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private CategoryRepository $categoryRepository,
        private SubCategoryRepository $subCategoryRepository,
        private PurchaseRepository $purchaseRepository,
        private ReviewRepository $reviewRepository,
        private UserRepository $userRepository
    )
    {

    }

    #[Route('/admin', name: 'admin_home')]
    public function index(): Response
    {
        $productCount = $this->productRepository->count([]);
        $noStockCount = $this->productRepository->count(['stock' => 0]);
        $categoryCount = $this->categoryRepository->count([]);
        $subCategoryCount = $this->subCategoryRepository->count([]);
        $reviewsPending = $this->reviewRepository->count(['moderationStatus' => null]);
        $purchasesInProcessCount = $this->purchaseRepository->countPurchasesInProcess();
        $userCount = $this->userRepository->count([]) - 1; // - 1 pour décompter l'admin

        return $this->render('admin/home/index.html.twig', [
            'product_count' => $productCount,
            'noStock_count' => $noStockCount,
            'category_count' => $categoryCount,
            'subCategory_count' => $subCategoryCount,
            'reviews_pending_count' => $reviewsPending,
            'purchases_in_process_count' => $purchasesInProcessCount,
            'user_count' => $userCount
        ]);
    }
}