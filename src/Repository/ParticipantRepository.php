<?php

namespace App\Repository;

use App\Entity\Participant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Participant>
 */
class ParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    //    /**
    //     * @return Participant[] Returns an array of Participant objects
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

        public function findOneByPseudo($value): Participant
        {
            return $this->createQueryBuilder('p')
                ->andWhere('p.pseudo = :pseudo')
                ->setParameter('pseudo', $value)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        }

        /**
        * @return Participant[] Returns an array of Participant objects
        */
        public function findByUser($username, $password): array
        {
            return $this->createQueryBuilder('p')
                ->andWhere('p.pseudo = :pseudo')
                ->andWhere('p.motDePasse = :mdp')
                ->setParameter('pseudo', $username)
                ->setParameter('mdp', $password)
                ->getQuery()
                ->getResult()
            ;
        }

       /* public function findByUser($username, $password): Participant
        {
            return $this->createQueryBuilder('p')
                ->andWhere('p.pseudo = :pseudo')
                ->andWhere('p.mot_de_passe = :mdp')
                ->setParameter('pseudo', $username)
                ->setParameter('mdp', $password)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        }*/


}
