<?php

namespace App\Repository;

use App\Entity\Enregistrement;
use App\Entity\EnregistrementGroup;
use App\Entity\LigneArret;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Enregistrement>
 */
class EnregistrementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Enregistrement::class);
    }

//    /**
//     * @return Enregistrement[] Returns an array of Enregistrement objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Enregistrement
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findLatestEnregistrementDirection1ForLigneArret(LigneArret $ligneArret): ?Enregistrement
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.ligneArretDirection1 = :ligneArret')
            ->setParameter('ligneArret', $ligneArret)
            ->orderBy('e.dateTime', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLatestEnregistrementDirection2ForLigneArret(LigneArret $ligneArret): ?Enregistrement
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.ligneArretDirection2 = :ligneArret')
            ->setParameter('ligneArret', $ligneArret)
            ->orderBy('e.dateTime', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findEnregistrementDirection1ForEnregistrementGroupAndLigneArret(EnregistrementGroup $enregistrementGroup, LigneArret $ligneArret): ?Enregistrement
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.enregistrementGroup = :enregistrementGroup')
            ->setParameter('enregistrementGroup', $enregistrementGroup)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
