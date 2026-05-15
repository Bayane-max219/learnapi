<?php

namespace App\Repository;

use App\Entity\QuizQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class QuizQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuizQuestion::class);
    }

    public function findByQuiz(int $quizId): array
    {
        return $this->createQueryBuilder('qq')
            ->andWhere('qq.quiz = :quizId')
            ->setParameter('quizId', $quizId)
            ->orderBy('qq.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countByQuiz(int $quizId): int
    {
        return (int) $this->createQueryBuilder('qq')
            ->select('COUNT(qq.id)')
            ->andWhere('qq.quiz = :quizId')
            ->setParameter('quizId', $quizId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
