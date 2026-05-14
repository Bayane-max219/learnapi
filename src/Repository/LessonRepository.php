<?php

namespace App\Repository;

use App\Entity\Lesson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LessonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lesson::class);
    }

    public function findByCourseOrdered(int $courseId): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.course = :courseId')
            ->setParameter('courseId', $courseId)
            ->orderBy('l.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
