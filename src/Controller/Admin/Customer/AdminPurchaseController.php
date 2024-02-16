<?php
namespace App\Controller\Admin\Customer;

use App\Form\Admin\PurchaseFilterType;
use App\Repository\PurchaseRepository;
use App\Form\Admin\DataModel\PurchaseFilter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Liip\ImagineBundle\Exception\Config\Filter\NotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_ADMIN')]
class AdminPurchaseController extends AbstractController
{
    public function __construct(
        private PurchaseRepository $purchaseRepository
    )
    {

    }

    #[Route('/admin/purchase/index', name: 'admin_purchase_index')]
    public function index(Request $request): Response
    {
        $purchaseFilter = new PurchaseFilter;
        $form = $this->createForm(PurchaseFilterType::class, $purchaseFilter);
        $form->handleRequest($request);

        $pagination = $this->purchaseRepository->adminFilter($request, $purchaseFilter, 20);

        return $this->render('admin/customer/purchase/index.html.twig', [
            'count_purchases' => $this->purchaseRepository->countPurchasesInProcess(),
            'pagination' => $pagination,
            'form' => $form->createView()
        ]);
    }

    /**
     * Show avec update du Purchase.status en js
     */
    #[Route('/admin/purchase/{id}/show', name: 'admin_purchase_show')]
    public function show(int $id): Response
    {
        $purchase = $this->purchaseRepository->find($id);
        if(!$purchase)
        {
            throw new NotFoundException('Aucune purchase avec l\'id'.$id);
        }
        return $this->render('admin/customer/purchase/show.html.twig', [
            'purchase' => $purchase,
            'count_purchases' => $this->purchaseRepository->countPurchasesInProcess()
        ]);
    }
}