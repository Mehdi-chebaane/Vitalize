<?php

namespace App\Repository;

use App\Entity\PublicationView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PublicationView>
 *
 * @method PublicationView|null find($id, $lockMode = null, $lockVersion = null)
 * @method PublicationView|null findOneBy(array $criteria, array $orderBy = null)
 * @method PublicationView[]    findAll()
 * @method PublicationView[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PublicationViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PublicationView::class);
    }

//    /**
//     * @return PublicationView[] Returns an array of PublicationView objects
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

//    public function findOneBySomeField($value): ?PublicationView
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
