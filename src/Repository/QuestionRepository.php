<?php

namespace App\Repository;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Question>
 */
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    //    /**
    //     * @return Question[] Returns an array of Question objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('q.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Question
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findQuestionsWithResponsesByUser(int $userId)
{
    return $this->createQueryBuilder('q')
        ->leftJoin('q.reponses', 'r') // Jointure avec la table des réponses
        ->addSelect('r') // Sélectionne également les réponses pour éviter les requêtes supplémentaires
        ->where('q.patient = :userId') // Filtrer les questions par patient
        ->setParameter('userId', $userId)
        ->getQuery()
        ->getResult();
}

public function findBySearchQuery(string $query)
{
    return $this->createQueryBuilder('q')
        ->where('q.titre LIKE :query OR q.contenu LIKE :query')
        ->setParameter('query', '%' . $query . '%')  // Assure-toi que le paramètre est bien encodé
        ->getQuery()
        ->getResult();
}


}
