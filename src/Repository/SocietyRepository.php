<?php

namespace App\Repository;

use App\Entity\Society;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Society>
 */
class SocietyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Society::class);
    }

    //    /**
    //     * @return Society[] Returns an array of Society objects
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

    public function getOneWithCollection(int $id_society): ?Society
    {
        return $this->createQueryBuilder('s')
            ->join("s.users", "us")
            ->addSelect("us")
            ->where('s.id = :val')
            ->setParameter('val', $id_society)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
