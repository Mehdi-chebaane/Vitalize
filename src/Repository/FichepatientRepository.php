<?php

namespace App\Repository;

use App\Entity\Fichepatient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Fichepatient>
 *
 * @method Fichepatient|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fichepatient|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fichepatient[]    findAll()
 * @method Fichepatient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FichepatientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fichepatient::class);
    }

//    /**
//     * @return Fichepatient[] Returns an array of Fichepatient objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Fichepatient
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
