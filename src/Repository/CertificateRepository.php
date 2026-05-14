<?php

namespace App\Repository;

use App\Entity\Certificate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CertificateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Certificate::class);
    }

    public function findByUuid(string $uuid): ?Certificate
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    public function findByStudent(int $studentId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.student = :studentId')
            ->setParameter('studentId', $studentId)
            ->orderBy('c.issuedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
