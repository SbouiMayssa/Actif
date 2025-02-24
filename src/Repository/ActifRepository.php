<?php

namespace App\Repository;

use App\Entity\Actif;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Actif>
 */
class ActifRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Actif::class);
    }

      /*Rechercher un actif par son nom (utilisé pour la recherche)
     public function searchByName(string $name): array
     {
         return $this->createQueryBuilder('a')
             ->where('a.nom LIKE :name')
             ->setParameter('name', '%' . $name . '%')
             ->getQuery()
             ->getResult();
     }*/


     public function searchByNumSerie(string $numSerie): array
     {
         return $this->createQueryBuilder('a')
             ->where('a.numSerie LIKE :numSerie')
             ->andWhere('a.DeletedAt IS NULL')
             ->andWhere('a.etat = :etat')
             ->setParameter('etat', 'en panne')
             ->setParameter('numSerie', '%' . $numSerie . '%')
             ->getQuery()
             ->getResult();
     }

    public function searchByNumSerieActif(string $numSerie): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.numSerie LIKE :numSerie')
            ->andWhere('a.DeletedAt IS NULL')
            ->setParameter('numSerie', '%' . $numSerie . '%')
            ->getQuery()
            ->getResult();
    }




    //  Obtenir les actifs en panne
     public function findActifsEnPanne(): array
     {
         return $this->createQueryBuilder('a')
             ->where('a.etat = :etat')
             ->andWhere('a.DeletedAt IS NULL')
             ->setParameter('etat', 'en panne')
             ->getQuery()
             ->getResult();
     }
 
     //  Trier les actifs par type
     public function findAllSortedByType(): array
     {
         return $this->createQueryBuilder('a')
             ->where('a.DeletedAt IS NULL')
             ->orderBy('a.type', 'ASC')
             ->getQuery()
             ->getResult();
     }
 
     //  Trier les actifs par date d’acquisition (du plus récent au plus ancien)
     public function findAllSortedByDateAcquisation(): array
     {
         return $this->createQueryBuilder('a')
             ->where('a.DeletedAt IS NULL')
             ->orderBy('a.dateAcquisation', 'DESC')
             ->getQuery()
             ->getResult();
     }


     public function findActifsEnPanneSortedByType(): array
{
    return $this->createQueryBuilder('a')
        ->where('a.etat = :etat')
        ->andWhere('a.DeletedAt IS NULL')
        ->setParameter('etat', 'en panne')
        ->orderBy('a.type', 'ASC')
        ->getQuery()
        ->getResult();
}

public function findActifsEnPanneSortedByDateAcquisation(): array
{
    return $this->createQueryBuilder('a')
        ->where('a.etat = :etat')
        ->andWhere('a.DeletedAt IS NULL')
        ->setParameter('etat', 'en panne')
        ->orderBy('a.dateAcquisation', 'DESC')
        ->getQuery()
        ->getResult();
}

 
     public function findByEtat(string $etat): array
     {
         return $this->createQueryBuilder('a')
             ->where('a.etat = :etat')
             ->andWhere('a.DeletedAt IS NULL')
             ->setParameter('etat', $etat)
             ->getQuery()
             ->getResult();
     }

     public function findAllArchived(): array
     {
         return $this->createQueryBuilder('a')
             ->where('a.DeletedAt IS NOT NULL') 
             ->getQuery()
             ->getResult();
     }

     public function countActiveAssets()
     {
         return $this->createQueryBuilder('a')
             ->select('COUNT(a)')
             ->where('a.etat = :etat')
             ->setParameter('etat', 'fonctionnel')
             ->getQuery()
             ->getSingleScalarResult();
     }

     public function countFaultyAssets()
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->where('a.etat = :etat')
            ->setParameter('etat', 'en panne')
            ->getQuery()
            ->getSingleScalarResult();
    }

     public function countReplacedAssets()
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->where('a.etat = :etat')
            ->setParameter('etat', 'remplacé')
            ->getQuery()
            ->getSingleScalarResult();
    }
    
}
