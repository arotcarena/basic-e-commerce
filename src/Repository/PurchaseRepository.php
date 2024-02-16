<?php

namespace App\Repository;

use App\Config\SiteConfig;
use App\Entity\Cart;
use App\Entity\Purchase;
use App\Entity\User;
use App\Form\Admin\DataModel\PurchaseFilter;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends ServiceEntityRepository<Purchase>
 *
 * @method Purchase|null find($id, $lockMode = null, $lockVersion = null)
 * @method Purchase|null findOneBy(array $criteria, array $orderBy = null)
 * @method Purchase[]    findAll()
 * @method Purchase[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PurchaseRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private PaginatorInterface $paginator,
        private ProductRepository $productRepository
    )
    {
        parent::__construct($registry, Purchase::class);
    }

    public function save(Purchase $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Purchase $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Undocumented function
     *
     * @param integer $id
     * @return array products indexés par Id
     */
    public function findPurchaseProducts(int $id): array
    {
        $productIds = $this->createQueryBuilder('p')
                            ->select('pl.product as product')
                            ->join('p.purchaseLines', 'pl')
                            ->where('p.id = :id')
                            ->setParameter('id', $id)
                            ->getQuery()
                            ->getResult()
                            ;
        
        return $this->productRepository->findByIdGroup($productIds);
    }

    /**
     * Return null if no paid Purchase exists with same user and same Lines (and less than one day ago)
     * Return this one if exists
     */
    public function findDuplicateNotPendingPurchase(Cart $cart, User $user): ?Purchase
    {
        /** @var Purchase[] */
        $purchases = $this->createQueryBuilder('p')
                            ->andWhere('p.user = :user')
                            ->setParameter('user', $user)
                            ->andWhere('p.totalPrice = :totalPrice')
                            ->setParameter('totalPrice', $cart->getTotalPrice())
                            ->andWhere('p.status != :status')
                            ->setParameter('status', SiteConfig::STATUS_PENDING)
                            ->andWhere('p.createdAt > :yesterday')
                            ->setParameter('yesterday', new DateTimeImmutable("now - 2 day"))
                            ->getQuery()
                            ->getResult()
                            ;
        if(count($purchases) === 0)
        {
            return null;
        }
        $cartLines = [];
        foreach($cart->getCartLines() as $cartLine)
        {
            $cartLines[$cartLine->getProduct()->getId()] = $cartLine->getQuantity();
        }
        foreach($purchases as $purchase)
        {
            $purchaseLines = [];
            foreach($purchase->getPurchaseLines() as $purchaseLine)
            {
                $purchaseLines[$purchaseLine->getProduct()['id']] = $purchaseLine->getQuantity();
            }
            if((array_keys($cartLines) === array_keys($purchaseLines)) && (array_values($cartLines) === array_values($purchaseLines)))
            {
                return $purchase;
            }
        }
        return null;
    }


    public function countPurchasesInProcess(): int
    {
        return $this->createQueryBuilder('p')
                    ->select('COUNT(p.id) as count')
                    ->where('p.status IN(:status)')
                    ->setParameter('status', [
                        SiteConfig::STATUS_PENDING, SiteConfig::STATUS_PAID, SiteConfig::STATUS_SENT
                    ])
                    ->getQuery()
                    ->getOneOrNullResult()['count']
                    ;
    }

    public function adminFilter(Request $request, PurchaseFilter $purchaseFilter, int $limit = 20): PaginationInterface
    {
        $qb = $this->createQueryBuilder('p')
                        ->select('p', 'u')
                        ->leftJoin('p.user', 'u')
                        ->orderBy('p.createdAt', 'DESC') // par défaut : ceci peut être modifié dans applyAdminFilters si purchaseFilter.sortBy === 'createdAt_ASC'
                        ;
        
        $this->applyAdminFilters($qb, $purchaseFilter);

        $this->applyAdminSort($qb, $purchaseFilter);

        $query = $qb->getQuery();

        $pagination = $this->paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            $limit /*limit per page*/
        );

        return $pagination;
    }

    private function applyAdminFilters(QueryBuilder $qb, PurchaseFilter $purchaseFilter): void 
    {
        if($purchaseFilter->getStatus() && $purchaseFilter->getStatus() !== '')
        {
            $qb->where('p.status = :status')
                ->setParameter('status', $purchaseFilter->getStatus())
                ;
        }
    }
    private function applyAdminSort(QueryBuilder $qb, PurchaseFilter $purchaseFilter): void 
    {
        if($purchaseFilter->getSortBy() === 'createdAt_ASC')
        {
            $qb->orderBy('p.createdAt', 'ASC');
        }
        //sinon on laisse le orderBy par défaut (createdAt_DESC)
    }


//    /**
//     * @return Purchase[] Returns an array of Purchase objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Purchase
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
