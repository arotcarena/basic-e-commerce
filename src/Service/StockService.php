<?php
namespace App\Service;

use App\Entity\Product;
use App\Entity\Purchase;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PurchaseLineRepository;
use App\Repository\PurchaseRepository;
use Doctrine\Common\Collections\Collection;

class StockService
{
    


    public function __construct(
        private EntityManagerInterface $em,
        private PurchaseRepository $purchaseRepository
    )
    {

    }

    public function verifyPurchaseStocksAndPriceAndUpdateStocksIfOk(Purchase $purchase): bool 
    {
        $productsById = $this->purchaseRepository->findPurchaseProducts($purchase->getId());
        foreach($purchase->getPurchaseLines() as $purchaseLine)
        {
            //si le product a été supprimé
            if(!isset($productsById[$purchaseLine->getProduct()['id']]))
            {
                return false;
            }
            $product = $productsById[$purchaseLine->getProduct()['id']];
            //si le stock est insuffisant
            if($purchaseLine->getQuantity() > $product->getStock())
            {
                return false;
            }
            //si le prix a été modifié
            elseif($purchaseLine->getProduct()['price'] !== $product->getPrice())
            {
                return false;
            }

            //si tout est bon, on update le stock
            $product->setStock($product->getStock() - $purchaseLine->getQuantity());
            if($product->getStock() < 0) // ça ne peut pas arriver mais juste au cas où
            {
                $product->setStock(0);
            }
        }
        $this->em->flush();
        return true;
    }

}