<?php

namespace App\Repository;

use App\Entity\Reponse;
use App\Entity\Question;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reponse>
 */
class ReponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reponse::class);
    }

    //    /**
    //     * @return Reponse[] Returns an array of Reponse objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reponse
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findByMostVoted(Question $question): array
{
    return $this->createQueryBuilder('r')
        ->leftJoin('r.votes', 'v')
        ->select('r', 'COUNT(v.id) AS HIDDEN vote_count')
        ->where('r.question = :question')
        ->groupBy('r.id')
        ->orderBy('vote_count', 'DESC') // Trier par votes décroissants
        ->setParameter('question', $question)
        ->getQuery()
        ->getResult();
}
}
