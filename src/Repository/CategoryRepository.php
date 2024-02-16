<?php

namespace App\Repository;

use App\Entity\Category;
use App\Repository\SubCategoryRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private SubCategoryRepository $subCategoryRepository    
    )
    {
        parent::__construct($registry, Category::class);
    }

    public function save(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Undocumented function
     *
     * @return Category[]
     */
    public function findAllOrderedForMenuList()
    {
        return $this->createQueryBuilder('c')
                    ->select('c', 'sc')
                    ->leftJoin('c.subCategories', 'sc')
                    ->orderBy('c.listPosition', 'ASC')
                    ->addOrderBy('sc.listPosition', 'ASC')
                    ->getQuery()
                    ->getResult()
                    ;
    }

    public function findOneOrdered(int $id): ?Category
    {
        return $this->createQueryBuilder('c')
                    ->select('c', 'sc')
                    ->leftJoin('c.subCategories', 'sc')
                    ->where('c.id = :id')
                    ->setParameter('id', $id)
                    ->orderBy('c.listPosition', 'ASC')
                    ->addOrderBy('sc.listPosition', 'ASC')
                    ->getQuery()
                    ->getOneOrNullResult()
                    ;
    }

//    /**
//     * @return Category[] Returns an array of Category objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Category
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
