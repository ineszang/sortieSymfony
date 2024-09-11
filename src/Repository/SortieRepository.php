<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findBySearchParameters($site, $recherche, $dateStart, $dateEnd, $mesSorties, $mesInscriptions, $pasMesInscriptions, $sortiesFinies)
    {
        $queryBuilder = $this->createQueryBuilder('s');

        if ($site) {
            $queryBuilder->andWhere('s.site = :site')
                ->setParameter('site', $site);
        }

        if ($recherche) {
            $queryBuilder->andWhere('s.nomSortie LIKE :recherche')
                ->setParameter('recherche', '%'.$recherche.'%');
        }

        if ($dateStart) {
            $queryBuilder->andWhere('s.dateHeureDebut >= :dateStart')
                ->setParameter('dateStart', $dateStart);
        }

        if ($dateEnd) {
            $queryBuilder->andWhere('s.dateHeureFin <= :dateEnd')
                ->setParameter('dateEnd', $dateEnd);
        }

        // Ajoutez ici des conditions supplémentaires basées sur les autres paramètres si nécessaire

        return $queryBuilder->getQuery()->getResult();
    }

    public function findPublishedSorties()
    {
        return $this->createQueryBuilder('w')

            ->getQuery()
            ->getResult();

    }

    //    /**
    //     * @return Sortie[] Returns an array of Sortie objects
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

       public function findOneBySomeField($value): ?Sortie
        {
            return $this->createQueryBuilder('s')
                ->andWhere('s.id = :val')
                ->setParameter('val', $value)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        }
}
