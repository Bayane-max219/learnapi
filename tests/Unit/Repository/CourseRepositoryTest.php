<?php

namespace App\Tests\Unit\Repository;

use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class CourseRepositoryTest extends TestCase
{
    private function buildRepository(array $results = []): CourseRepository
    {
        $query = $this->createMock(AbstractQuery::class);
        $query->method('getResult')->willReturn($results);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('andWhere')->willReturnSelf();
        $qb->method('orderBy')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);

        $em = $this->createMock(EntityManagerInterface::class);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($em);

        $repo = $this->getMockBuilder(CourseRepository::class)
            ->setConstructorArgs([$registry])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        $repo->method('createQueryBuilder')->willReturn($qb);

        return $repo;
    }

    public function testFindPublishedReturnsArray(): void
    {
        $repo = $this->buildRepository([]);
        $result = $repo->findPublished();
        $this->assertIsArray($result);
    }

    public function testFindByCategoryReturnsArray(): void
    {
        $repo = $this->buildRepository([]);
        $result = $repo->findByCategory('programming');
        $this->assertIsArray($result);
    }

    public function testFindPublishedReturnsExpectedCourses(): void
    {
        $fakeCourse = new \stdClass();
        $fakeCourse->title = 'Django avancé';
        $repo = $this->buildRepository([$fakeCourse]);

        $result = $repo->findPublished();
        $this->assertCount(1, $result);
    }
}
