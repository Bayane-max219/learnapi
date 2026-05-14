<?php

namespace App\Repository;

use App\Entity\Enrollment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EnrollmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Enrollment::class);
    }

    public function findByStudent(int $studentId): array
    {
        return $this->createQueryBuilder('e')
            ->join('e.course', 'c')
            ->andWhere('e.student = :studentId')
            ->setParameter('studentId', $studentId)
            ->orderBy('e.enrolledAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveByStudent(int $studentId): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.student = :studentId')
            ->andWhere('e.status = :status')
            ->setParameter('studentId', $studentId)
            ->setParameter('status', 'active')
            ->getQuery()
            ->getResult();
    }
}
