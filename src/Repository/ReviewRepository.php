<?php

namespace App\Repository;

use App\Config\SiteConfig;
use App\Entity\Review;
use App\Form\Admin\DataModel\ReviewFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends ServiceEntityRepository<Review>
 *
 * @method Review|null find($id, $lockMode = null, $lockVersion = null)
 * @method Review|null findOneBy(array $criteria, array $orderBy = null)
 * @method Review[]    findAll()
 * @method Review[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private PaginatorInterface $paginator
    )
    {
        parent::__construct($registry, Review::class);
    }

    public function save(Review $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Review $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function adminFilter(Request $request, ReviewFilter $reviewFilter, int $limit = 20): PaginationInterface
    {
        $qb = $this->createQueryBuilder('r')
                    ->orderBy('r.createdAt', 'DESC')
                    ;

        $this->applyAdminFilters($reviewFilter, $qb);

        $this->applyAdminSort($reviewFilter, $qb);

        $query = $qb->getQuery();


        $pagination = $this->paginator->paginate(
            $query,
            $request->query->get('page', 1),
            $limit
        );
        return $pagination;
    }

    private function applyAdminFilters(ReviewFilter $reviewFilter, QueryBuilder $qb): void
    {
        if($reviewFilter->getRate())
        {
            $qb->andWhere('r.rate = :rate')
                ->setParameter('rate', $reviewFilter->getRate());
        }
        if($reviewFilter->getModerationStatus())
        {
            if($reviewFilter->getModerationStatus() === SiteConfig::MODERATION_STATUS_PENDING) // on fait ça parceque review.moderationStatus prend la valeur null pour pending
            {
                $qb->andWhere('r.moderationStatus IS NULL')
                    ;
            }
            else
            {
                $qb->andWhere('r.moderationStatus = :moderationStatus')
                    ->setParameter('moderationStatus', $reviewFilter->getModerationStatus())
                    ;
            }
            
        }
    }

    private function applyAdminSort(ReviewFilter $reviewFilter, QueryBuilder $qb): void
    {
        if($reviewFilter->getSortBy())
        {
            switch($reviewFilter->getSortBy())
            {
                case 'rate_ASC':
                    $qb->orderBy('r.rate', 'ASC');
                break;
                case 'rate_DESC':
                    $qb->orderBy('r.rate', 'DESC');
                break;
                case 'createdAt_ASC':
                    $qb->orderBy('r.createdAt', 'ASC');
                break;
                default:
                    $qb->orderBy('r.createdAt', 'DESC');  // déjà par défaut
            }
        }
    }

//    /**
//     * @return Review[] Returns an array of Review objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Review
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
