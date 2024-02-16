<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\SubCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SubCategory>
 *
 * @method SubCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubCategory[]    findAll()
 * @method SubCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubCategory::class);
    }

    public function save(SubCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SubCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByBothSlugs(string $categorySlug, string $subCategorySlug): ?SubCategory
    {
        return $this->createQueryBuilder('sc')
                    ->join('sc.parentCategory', 'c')
                    ->where('c.slug = :categorySlug')
                    ->andWhere('sc.slug = :subCategorySlug')
                    ->setParameter('categorySlug', $categorySlug)
                    ->setParameter('subCategorySlug', $subCategorySlug)
                    ->getQuery()
                    ->getOneOrNullResult()
                    ;
    }

    public function slugExistsWithParentCategory(Category $parentCategory, string $slug): bool 
    {
        $result = $this->createQueryBuilder('sc')
                        ->where('sc.slug = :slug')
                        ->setParameter('slug', $slug)
                        ->andWhere('sc.parentCategory = :parentCategory')
                        ->setParameter('parentCategory', $parentCategory)
                        ->getQuery()
                        ->getOneOrNullResult()
                        ;
        return $result !== null;
    }

   

//    /**
//     * @return SubCategory[] Returns an array of SubCategory objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SubCategory
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
