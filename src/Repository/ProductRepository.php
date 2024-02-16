<?php

namespace App\Repository;

use App\Entity\Cart;
use App\Entity\Product;
use Doctrine\ORM\QueryBuilder;
use App\Form\DataModel\SearchParams;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\Admin\DataModel\ProductFilter;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private PictureRepository $pictureRepository,
        private PaginatorInterface $paginator
    )
    {
        parent::__construct($registry, Product::class);
    }

    public function save(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    /**
     * @return Product[]
     */
    public function filter(SearchParams $params)
    {
        $qb = $this->createQueryBuilder('p')
                    ->select('p', 'c', 'sc')
                    ->leftJoin('p.category', 'c')
                    ->leftJoin('p.subCategory', 'sc')
                    ->where('p.stock > 0')
                    ;
        
        $this->applyFilters($qb, $params);

        $this->applySort($qb, $params);
        
        // $this->applyPagination($qb, $params);
        
        $products = $qb
                    ->getQuery()
                    ->getResult()
                    ;
        $this->pictureRepository->hydrateProductsWithFirstPicture($products);
        return $products;
    }


    public function countFilter(SearchParams $searchParams): int
    {
        $qb = $this->createQueryBuilder('p')
                    ->select('COUNT(p.id) as count')
                    ->leftJoin('p.category', 'c')
                    ->leftJoin('p.subCategory', 'sc')
                    ->where('p.stock > 0')
                    ;
        $this->applyFilters($qb, $searchParams);
                    
        return $qb
                ->getQuery()
                ->getOneOrNullResult()['count']
                ;
    }

    /**
     *
     * @param string $q
     * @param integer $limit
     * @return Product[]
     */
    public function qSearch(string $q, int $limit = 4)
    {
        /** @var Product[] */
        $products = $this->createQueryBuilder('p')
                    ->select('p', 'c', 'sc')
                    ->leftJoin('p.category', 'c')
                    ->leftJoin('p.subCategory', 'sc')
                    ->where('p.stock > 0')
                    ->andWhere('(p.designation LIKE :q OR c.name LIKE :q OR sc.name LIKE :q)')
                    ->setParameter('q', '%'.$q.'%')
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult()
                    ;
        $this->pictureRepository->hydrateProductsWithFirstPicture($products);
        return $products;
    }

    public function countQSearch(string $q): int 
    {
        return $this->createQueryBuilder('p')
                    ->select('COUNT(p.id) as count')
                    ->leftJoin('p.category', 'c')
                    ->leftJoin('p.subCategory', 'sc')
                    ->where('p.stock > 0')
                    ->andWhere('(p.designation LIKE :q OR c.name LIKE :q OR sc.name LIKE :q)')
                    ->setParameter('q', '%'.$q.'%')
                    ->getQuery()
                    ->getOneOrNullResult()['count']
                    ;
    }

    public function findOneByPublicRef(string $publicRef): Product
    {
        /** @var Product */
        $product = $this->createQueryBuilder('p')
                        ->select('p')
                        ->where('p.publicRef = :publicRef')
                        ->setParameter('publicRef', $publicRef)
                        ->getQuery()
                        ->getOneOrNullResult()
                        ;
        $this->pictureRepository->hydrateProductsWithFirstPicture([$product]);
        return $product;
    }

    /**
     *  utilisé par le cartService pour récupérer tous les produits présents dans le panier ou par stockService (donc pas besoin de where stock > 0)
     * @param int[] $ids
     * 
     * @return Product[] Products indexés par Id
     */
    public function findByIdGroup(array $ids)
    {
        $products = $this->createQueryBuilder('p')
                    ->select('p', 'c', 'sc')
                    ->leftJoin('p.category', 'c')
                    ->leftJoin('p.subCategory', 'sc')
                    ->where('p.id IN(:ids)')
                    ->setParameter('ids', $ids)
                    ->getQuery()
                    ->getResult()
                    ;

        //on indexe les products par id
        $productsById = [];
        foreach($products as $product)
        {
            $productsById[$product->getId()] = $product;
        }
        return $productsById;
    }

    private function applyFilters(QueryBuilder $qb, SearchParams $params): void
    {
        if($params->getCategoryId())
        {
            $qb->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $params->getCategoryId())
                ;
        }
        if($params->getSubCategoryId())
        {
            $qb->andWhere('sc.id = :subCategoryId')
                ->setParameter('subCategoryId', $params->getSubCategoryId())
                ;
        }
        if($params->getMaxPrice() !== null)
        {
            $qb->andWhere('p.price <= :maxPrice')
                ->setParameter('maxPrice', $params->getMaxPrice())
                ;
        }
        if($params->getMinPrice() !== null)
        {
            $qb->andWhere('p.price >= :minPrice')
                ->setParameter('minPrice', $params->getMinPrice())
                ;
        }
        if($params->getQ() !== null && $params->getQ() !== '')
        {
            $qb->andWhere('(p.designation LIKE :q OR c.name LIKE :q OR sc.name LIKE :q)')
                ->setParameter('q', '%'.$params->getQ().'%')
                ;
        }
    }

    private function applySort(QueryBuilder $qb, SearchParams $params): void 
    {
        if($params->getSort() === 'createdAt_ASC')
        {
            $qb->orderBy('p.createdAt', 'ASC');
        }
        if($params->getSort() === 'createdAt_DESC')
        {
            $qb->orderBy('p.createdAt', 'DESC');
        }
        if($params->getSort() === 'price_ASC')
        {
            $qb->orderBy('p.price', 'ASC');
        }
        if($params->getSort() === 'price_DESC')
        {
            $qb->orderBy('p.price', 'DESC');
        }
    }



    //ADMIN

    public function adminFilter(Request $request, ProductFilter $productFilter): PaginationInterface
    {
        $qb = $this->createQueryBuilder('p')
        ->select('p', 'c', 'sc')
        ->leftJoin('p.category', 'c')
        ->leftJoin('p.subCategory', 'sc')
        ;

        $this->applyAdminFilters($qb, $productFilter);

        $this->applyAdminSort($qb, $productFilter);

        /** @var PaginationInterface */
        $pagination = $this->paginator->paginate(
            $qb->getQuery(), /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            20 /*limit per page*/
        );

        $this->pictureRepository->hydrateProductsWithFirstPicture($pagination->getItems());
        return $pagination;
    }

    private function applyAdminFilters(QueryBuilder $qb, ProductFilter $productFilter)
    {
        if($productFilter->getCategory())
        {
            $qb->andWhere('p.category = :category')
                ->setParameter('category', $productFilter->getCategory())
                ;
        }
        if($productFilter->getSubCategory())
        {
            $qb->andWhere('p.subCategory = :subCategory')
                ->setParameter('subCategory', $productFilter->getSubCategory())
                ;
        }
        if($productFilter->getMaxPrice() !== null)
        {
            $qb->andWhere('p.price <= :maxPrice')
                ->setParameter('maxPrice', $productFilter->getMaxPrice())
                ;
        }
        if($productFilter->getMinPrice() !== null)
        {
            $qb->andWhere('p.price >= :minPrice')
                ->setParameter('minPrice', $productFilter->getMinPrice())
                ;
        }
        if($productFilter->getMaxStock() !== null)
        {
            $qb->andWhere('p.stock <= :maxStock')
                ->setParameter('maxStock', $productFilter->getMaxStock())
                ;
        }
        if($productFilter->getMinStock() !== null)
        {
            $qb->andWhere('p.stock >= :minStock')
                ->setParameter('minStock', $productFilter->getMinStock())
                ;
        }
        if($productFilter->getQ() !== null && $productFilter->getQ() !== '')
        {
            $qb->andWhere('(p.designation LIKE :q OR c.name LIKE :q OR sc.name LIKE :q)')
                ->setParameter('q', '%'.$productFilter->getQ().'%')
                ;
        }
    }

    private function applyAdminSort(QueryBuilder $qb, ProductFilter $productFilter): void 
    {
        if($productFilter->getSortBy() === 'createdAt_ASC')
        {
            $qb->orderBy('p.createdAt', 'ASC');
        }
        if($productFilter->getSortBy() === 'createdAt_DESC')
        {
            $qb->orderBy('p.createdAt', 'DESC');
        }
        if($productFilter->getSortBy() === 'price_ASC')
        {
            $qb->orderBy('p.price', 'ASC');
        }
        if($productFilter->getSortBy() === 'price_DESC')
        {
            $qb->orderBy('p.price', 'DESC');
        }
    }

//    /**
//     * @return Product[] Returns an array of Product objects
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

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
