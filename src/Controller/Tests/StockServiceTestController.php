<?php
namespace App\Controller\Tests;

use App\Repository\PurchaseRepository;
use App\Service\StockService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StockServiceTestController extends AbstractController
{

    public function __construct(
        private StockService $stockService,
        private PurchaseRepository $purchaseRepository
    )
    {

    }

    /**
     * sers à StockServiceFunctionalTest pour tester nb de query db
     */
    #[Route('tests/stockService/verifyAndUpdateStocks/{purchaseId}', name: 'tests_stockService_verifyAndUpdateStocks')]
    public function verifyAndUpdateStocks(int $purchaseId): JsonResponse
    {
        $purchase = $this->purchaseRepository->find($purchaseId);
        $result = $this->stockService->verifyPurchaseStocksAndPriceAndUpdateStocksIfOk($purchase);
        return $this->json($result);
    }
}