<?php

namespace App\Repository;

use App\Entity\Picture;
use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Picture>
 *
 * @method Picture|null find($id, $lockMode = null, $lockVersion = null)
 * @method Picture|null findOneBy(array $criteria, array $orderBy = null)
 * @method Picture[]    findAll()
 * @method Picture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PictureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Picture::class);
    }

    public function save(Picture $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Picture $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * recherche dans les pictures liées à chaque product la picture avec le listPosition le plus faible, et la place dans la propriété firstPicture du product
     * @param Product[] $products
     * @return void
     */
    public function hydrateProductsWithFirstPicture($products): void
    {
        $pictures = $this->createQueryBuilder('pic')
                            ->where('pic.product IN(:products)')
                            ->andWhere('pic.listPosition = 1')
                            ->setParameter('products', $products)
                            ->getQuery()
                            ->getResult()
                            ;
                            
        /** @var Product[] */
        $productsById = [];
        foreach($products as $product)
        {
            $productsById[$product->getId()] = $product;
        }
        foreach($pictures as $picture)
        {
            $productsById[$picture->getProduct()->getId()]->setFirstPicture($picture);
        }
    }

//    /**
//     * @return Picture[] Returns an array of Picture objects
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

//    public function findOneBySomeField($value): ?Picture
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
