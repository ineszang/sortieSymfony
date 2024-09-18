<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    private $connection;
    public function __construct(ManagerRegistry $registry, Connection $connection)
    {
        parent::__construct($registry, Sortie::class);
        $this->connection = $connection;
    }

    public function findBySearchParameters($site, $recherche, $dateStart, $dateEnd, $mesSorties, $mesInscriptions, $pasMesInscriptions, $sortiesFinies, $userId, ?string $etat = null )
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
                ->groupBy('s.id')
                ->having('SUM(CASE WHEN p2.id = :userId THEN 1 ELSE 0 END) = 0')
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

        if ($etat) {
            $queryBuilder->join('s.etat', 'e') // Jointure sur l'état de la sortie
            ->andWhere('e.libelle = :etat')
                ->setParameter('etat', $etat);
        }

        return $queryBuilder->getQuery()->getResult();
    }
    public function findParticipantsBySortieId(int $id)
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.participants', 'p')
            ->addSelect('p')
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
            ?->getParticipants();
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

    public function addInscription(int $userId, int $sortieId): void
    {
        $sql = 'INSERT INTO sortie_participant (participant_id, sortie_id) VALUES (:user_id, :sortie_id)';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('user_id', $userId);
        $stmt->bindValue('sortie_id', $sortieId);
        $stmt->execute();
    }

    public function removeInscription(int $userId, int $sortieId): void
    {
        $sql = 'DELETE FROM sortie_participant WHERE participant_id = :user_id AND sortie_id = :sortie_id';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('user_id', $userId);
        $stmt->bindValue('sortie_id', $sortieId);
        $stmt->execute();
    }

    public function checkSortie($idSortie, ?int $idUtilisateur): bool
    {
        // Récupère les informations de la sortie spécifiée
        $qb = $this->createQueryBuilder('s');
        $qb->select('s.dateHeureDebut', 's.dateHeureFin')
            ->where('s.id = :idSortie')
            ->setParameter('idSortie', $idSortie);

        $sortie = $qb->getQuery()->getOneOrNullResult();

        if (!$sortie) {
            // Si la sortie n'existe pas, retourner faux
            return false;
        }

        $dateDebut = $sortie['dateHeureDebut'];
        $dateFin = $sortie['dateHeureFin'];

        // Récupérer toutes les sorties de l'utilisateur et vérifier les chevauchements
        $qb = $this->createQueryBuilder('s');
        $qb->join('s.participants', 'p')
            ->where('p.id = :idUtilisateur')
            ->andWhere('s.dateHeureFin < :dateFin')
            ->orWhere('s.dateHeureDebut > :dateDebut')
            ->setParameter('idUtilisateur', $idUtilisateur)
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin);

        // Vérifie s'il y a des résultats
        $result = $qb->getQuery()->getResult();

        // Retourne true si des chevauchements sont trouvés, sinon false
        return !empty($result);
    }

    public function changeState(int $idSortie, $etatLibelle): void
    {
        // Récupérer l'état "Ouverte" à partir du libellé
        $etat = $this->getEntityManager()
            ->getRepository(Etat::class)
            ->findOneBy(['libelle' => $etatLibelle]);

        if (!$etat) {
            throw new \Exception('Etat non trouvé');
        }

        // Mettre à jour l'état de la sortie
        $this->createQueryBuilder('s')
            ->update() // Mise à jour de l'entité
            ->set('s.etat', ':etat') // Assignation de l'objet Etat
            ->where('s.id = :idSortie') // Condition sur l'id de la sortie
            ->setParameter('idSortie', $idSortie)
            ->setParameter('etat', $etat) // Paramètre pour l'objet Etat
            ->getQuery()
            ->execute(); // Exécuter la requête
    }

    public function getTotalParticipant(int $idSortie): int
    {
        $qb = $this->createQueryBuilder('s');
        $qb->select('COUNT(p.id)')
            ->join('s.participants', 'p')
            ->where('s.id = :idSortie')
            ->setParameter('idSortie', $idSortie);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
