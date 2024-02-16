<?php
namespace App\Service;

use App\Entity\Product;
use App\Entity\Purchase;
use App\Repository\ProductRepository;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ProductCountService
{
    public function __construct(
        private EntityManagerInterface $em,
        private RequestStack $requestStack,
        private PurchaseRepository $purchaseRepository
    )
    {

    }

    public function countView(Product $product)
    {
        $productViews = $this->requestStack->getSession()->get('productViews', []);
        if(in_array($product->getId(), $productViews))
        {
            return;
        }

        if($product->getCountViews() === null)
        {
            $product->setCountViews(1);
        }
        else
        {
            $product->setCountViews($product->getCountViews() + 1);
        }
        $this->em->flush();
        $productViews[] = $product->getId();
        $this->requestStack->getSession()->set('productViews', $productViews);
    }

    public function countCart(Product $product)
    {
        $productCarts = $this->requestStack->getSession()->get('productCarts', []);
        if(in_array($product->getId(), $productCarts))
        {
            return;
        }

        if($product->getCountCarts() === null)
        {
            $product->setCountCarts(1);
        }
        else
        {
            $product->setCountCarts($product->getCountCarts() + 1);
        }
        $this->em->flush();
        $productCarts[] = $product->getId();
        $this->requestStack->getSession()->set('productCarts', $productCarts);
    }

    public function countSales(Purchase $purchase)
    {
        $productsById = $this->purchaseRepository->findPurchaseProducts($purchase->getId());
        foreach($purchase->getPurchaseLines() as $purchaseLine)
        {
            if(isset($productsById[$purchaseLine->getProduct()['id']])) // normalement obligatoire mais on vérifie juste au cas ou
            {
                $product = $productsById[$purchaseLine->getProduct()['id']];
                if($product->getCountSales() === null)
                {
                    $product->setCountSales($purchaseLine->getQuantity());
                }
                else
                {
                    $product->setCountSales(
                        $product->getCountSales() + $purchaseLine->getQuantity()
                    );
                }
            }
        }
        $this->em->flush();
    }
}