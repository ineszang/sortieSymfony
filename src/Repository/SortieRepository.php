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

    public function findBySearchParameters($site, $recherche, $dateStart, $dateEnd, $mesSorties, $mesInscriptions, $pasMesInscriptions, $sortiesFinies, $userId)
    {
        $queryBuilder = $this->createQueryBuilder('s');

        // Filtrer par site
        if ($site) {
            $queryBuilder->andWhere('s.siteOrganisateur = :site')
                ->setParameter('site', $site);
        }

        // Filtrer par nom de sortie
        if ($recherche) {
            $queryBuilder->andWhere('s.nomSortie LIKE :recherche')
                ->setParameter('recherche', '%'.$recherche.'%');
        }

        // Filtrer par date de début
        if ($dateStart) {
            $queryBuilder->andWhere('s.dateHeureDebut >= :dateStart')
                ->setParameter('dateStart', $dateStart);
        }

            // Filtrer par date de fin
        if ($dateEnd) {
            $queryBuilder->andWhere('s.dateHeureFin <= :dateEnd')
                ->setParameter('dateEnd', $dateEnd);
        }

            // Filtrer par les sorties dont l'utilisateur est l'organisateur
        if ($mesSorties) {
            $queryBuilder->andWhere('s.organisateur = :userId')
                ->setParameter('userId', $userId);
        }

        // Filtrer par les sorties auxquelles l'utilisateur est inscrit
        if ($mesInscriptions) {
            $queryBuilder->innerJoin('s.participants', 'p1')
                ->andWhere('p1.id = :userId')
                ->setParameter('userId', $userId);
        }

        // Filtrer par les sorties auxquelles l'utilisateur n'est pas inscrit
        if ($pasMesInscriptions) {
            $queryBuilder->leftJoin('s.participants', 'p2')
                ->andWhere('(p2.id != :userId OR p2.id IS NULL)')
                ->andWhere('s.organisateur != :userId')
                ->setParameter('userId', $userId);
        }

            // Date limite pour les sorties finies depuis plus d'un mois
        $dateLimit = new \DateTime();
        $dateLimit->modify('-1 month');

            // Filtrer par les sorties passées
        if ($sortiesFinies) {
            $queryBuilder->andWhere('s.dateHeureFin < CURRENT_DATE()')
                ->andWhere('s.dateHeureFin >= :dateLimit')
                ->setParameter('dateLimit', $dateLimit);
        } else {
            // Inclure les futures et les en cours
            $queryBuilder->andWhere('s.dateHeureFin >= :dateLimit')
                ->setParameter('dateLimit', $dateLimit);
        }

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
