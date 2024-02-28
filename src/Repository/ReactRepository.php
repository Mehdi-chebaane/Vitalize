<?php

namespace App\Repository;

use App\Entity\Publication;
use App\Entity\React;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, React::class);
    }

    public function getTotalLikesForPublication(Publication $publication): int
    {
        return $this->createQueryBuilder('r')
            ->select('SUM(r.LikeCount)')
            ->andWhere('r.id_pub = :publication')
            ->setParameter('publication', $publication)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    }

    public function getTotalDislikesForPublication(Publication $publication): int
    {
        return $this->createQueryBuilder('r')
            ->select('SUM(r.DislikeCount)')
            ->andWhere('r.id_pub = :publication')
            ->setParameter('publication', $publication)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    }
}
