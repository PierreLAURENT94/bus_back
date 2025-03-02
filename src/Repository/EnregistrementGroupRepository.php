<?php

namespace App\Repository;

use App\Entity\EnregistrementGroup;
use App\Entity\Ligne;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EnregistrementGroup>
 */
class EnregistrementGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EnregistrementGroup::class);
    }

    //    /**
    //     * @return EnregistrementGroup[] Returns an array of EnregistrementGroup objects
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

    //    public function findOneBySomeField($value): ?EnregistrementGroup
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findLastedEnregistrementGroupForLigne(Ligne $ligne): ?EnregistrementGroup
    {
        return $this->createQueryBuilder(alias: 'e')
            ->andWhere('e.ligne = :ligne')
            ->setParameter('ligne', $ligne)
            ->orderBy('e.heure', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
