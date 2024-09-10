<?php

namespace App\Repository;

use App\Entity\Site;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Site>
 */
class SiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Site::class);
    }

   /* /**
    * @return Site[] Returns an array of Site objects
    */
    /*public function findById($value): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.id = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

        /**
         * @return Site[] Returns an array of Site objects
         */
      /*  public function findAll(): array
        {
           return $this->createQueryBuilder('s')
                ->orderBy('s.id', 'ASC')
                ->setMaxResults(3)
               ->getQuery()
               ->getResult()
            ;
        }*/

    //    public function findOneBySomeField($value): ?Site
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
