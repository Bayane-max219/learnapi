<?php

namespace App\Repository;

use App\Entity\Quiz;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class QuizRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quiz::class);
    }

    public function findByCourse(int $courseId): array
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.course = :courseId')
            ->setParameter('courseId', $courseId)
            ->andWhere('q.isActive = true')
            ->orderBy('q.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
