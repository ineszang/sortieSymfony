<?php

namespace App\Repository;

use App\Entity\Lieu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LieuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lieu::class);
    }

    /**
     * Rechercher les lieux par nom, rue, ville ou code postal.
     *
     * @param string $query La chaîne de recherche.
     * @return Lieu[] Un tableau d'objets Lieu.
     */
    public function searchLieux(string $query): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.ville', 'v')
            ->where('l.nomLieu LIKE :query OR l.rue LIKE :query OR v.nomVille LIKE :query OR v.codePostal LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }
}
